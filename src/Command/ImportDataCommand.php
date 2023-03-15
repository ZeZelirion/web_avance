<?php

namespace App\Command;

use App\Entity\Ability;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Repository\AbilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:get-pokemon-data',
    description: 'Fill DB with Pokemons',
)]
class ImportDataCommand extends Command {
	public function __construct(EntityManagerInterface $entityManager, AbilityRepository $abilityRepository, TypeRepository $typeRepository) {
		$this->em = $entityManager;
		$this->abilityRepository = $abilityRepository;
		$this->typeRepository = $typeRepository;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://pokeapi.co/api/v2/pokemon?limit=151");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output_api = curl_exec($curl);
		$json = json_decode($output_api, true);
		foreach ($json["results"] as $pokemon) {
			$newPokemon = new Pokemon();
			$newPokemon->setName($pokemon['name']);

			$curl2 = curl_init();
			curl_setopt($curl2, CURLOPT_URL, $pokemon['url']);
			curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
			$output_api2 = curl_exec($curl2);
			$pokemonData = json_decode($output_api2, true);

			$newPokemon->setHeight($pokemonData['height']);
			$newPokemon->setWeight($pokemonData['weight']);

			foreach ($pokemonData["abilities"] as $ability) {
				$abilityExists = $this->abilityRepository->findOneByName($ability['ability']['name']);
				if (!$abilityExists) {
					$newAbility = new Ability();
					$newAbility->setName($ability['ability']["name"]);
					$newAbility->addPokemon($newPokemon);
					$this->em->persist($newAbility);
					$newPokemon->addAbility($newAbility);
				} else {
					$abilityExists->addPokemon($newPokemon);
					$this->em->persist($abilityExists);
					$newPokemon->addAbility($abilityExists);
				}
			}
			foreach ($pokemonData["types"] as $type) {
				$typeExists = $this->typeRepository->findOneByName($type['type']['name']);
				if (!$typeExists) {
					$newType = new Type();
					$newType->setName($type['type']["name"]);
					$newType->addPokemon($newPokemon);
					$this->em->persist($newType);
					$newPokemon->addType($newType);
				} else {
					$typeExists->addPokemon($newPokemon);
					$this->em->persist($typeExists);
					$newPokemon->addType($typeExists);
				}
			}
			$this->em->persist($newPokemon);
			$this->em->flush();
			curl_close($curl2);
		}
		curl_close($curl);
		return Command::SUCCESS;
	}
}
