/**
 * 
 *  Gestion des publications
 *  
 */

import * as Constants from "./const.js";

/**
 * Récupère la liste des publications
 */
async function getPublications() {
    try {
        const response = await fetch(`${Constants.API_URL}/publications/index.php`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const publications = await response.json();
        _displayPublications(publications);
    } catch (error) {
        console.error(error);
    }
}

/**
 * Affiche en HTML la liste des publications
 * @param {json} publications - la liste des publications
 */
function _displayPublications(publications) {
    publicationsDiv.innerHTML = '';
    publications.forEach(publication => {
        const publicationElement = document.createElement('div');
        publicationElement.innerHTML = `
        <h2>${publication.title}</h2>
        <div><img src='img/${publication.image}'></div>
      `;
      publicationsDiv.appendChild(publicationElement);
    });
}

export { getPublications };