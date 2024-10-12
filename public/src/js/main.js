import { getPublications } from './publications.js';

import * as Constants from "./const.js";

/**
 * Affiche la bonne section selon l'état du token 
 */
function refresh() {
    // Si un token existe on cache la section login et on affiche la section publications
    if ("token" in localStorage) {
        loginSection.style.display = 'none';
        connectedSection.style.display = 'block';
        getPublications();
    }
    // sinon on l'affiche
    else {
        login.value = password.value = '';
        loginSection.style.display = 'block';
        connectedSection.style.display = 'none';
    }
}

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const response = await fetch(`${Constants.API_URL}/auth.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ login: login.value, password: password.value })
    });
    if (!response.ok) {
        statusMessage.innerText = "Nom d'utilisateur / mot de passe incorrect ou problème réseau; veuillez réessayer";
        login.focus();
    }
    else {
        const data = await response.json();
        statusMessage.innerText = ''; // cas où le précédent essai était infructueux
        localStorage.setItem("token", data.token);
    }
    refresh();

});

disconnect.addEventListener('click', () => {
    localStorage.clear(); // on supprime le token jwt
    refresh();
})

refresh()