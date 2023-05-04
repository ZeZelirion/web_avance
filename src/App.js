// src/App.js
import React from 'react';
import { BrowserRouter as Router, Switch, Route, Redirect } from 'react-router-dom';
import Home from './components/Home';
import Login from './components/Login';
import { useState } from 'react';
import axios from 'axios';

// lier chaque route à un composant
function App() {
  return (
    <Router>
      <Switch>
        <Route path="/login">
          <Login />
        </Route>
        <Route path="/home">
          <Home />
        </Route>
        <Redirect exact from="/" to="/home" />
      </Switch>
    </Router>
  );
}

// Bloquer l'accès à la home si non connecté
function Home() {
    const isLoggedIn = localStorage.getItem('access_token') !== null;
  
    if (!isLoggedIn) {
      return <Redirect to="/login" />;
    }
  
    return (
      <div>
        // Votre code pour la page d'accueil
      </div>
    );
  }  

//Formulaire de connexion
function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = (event) => {
    event.preventDefault();
    axios.post('/login_check', {
      username,
      password,
    }).then((response) => {
      localStorage.setItem('access_token', response.data.token);
      window.location.href = '/home';
    }).catch((error) => {
      console.log(error);
    });
  };

  return (
    <div>
      <h2>Login</h2>
      <form onSubmit={handleSubmit}>
        <label htmlFor="username">Username:</label>
        <input type="text" id="username" value={username} onChange={(event) => setUsername(event.target.value)} />
        <br />
        <label htmlFor="password">Password:</label>
        <input type="password" id="password" value={password} onChange={(event) => setPassword(event.target.value)} />
        <br />
        <button type="submit">Login</button>
      </form>
    </div>
  );
}

export default App;
