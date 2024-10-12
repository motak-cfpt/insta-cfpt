/**
 * Test des routes de l'API InstaCFPT
 */

const fetch = require('node-fetch');

function isJsonObject(obj) {
    return typeof obj === 'object' && obj !== null && !Array.isArray(obj) &&
        typeof JSON.stringify(obj) === 'string';
}

describe('API Tests', () => {
    const JWT_TOKEN_LENGTH = 149;

    const baseURL = 'http://api.insta.cfpt.info';

    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_METHOD_NOT_ALLOWED = 405;

    test("POST /auth.php avec login/password valables. Doit retourner le token jwt", async () => {

        const QUERY = { login: "Alice", password: "password1" };

        const response = await fetch(`${baseURL}/auth.php`, {
            method: 'POST', headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(QUERY)
        });
        const ANSWER = await response.json();
        expect(response.status).toBe(HTTP_OK);
        expect(isJsonObject(ANSWER)).toBe(true);
        expect('token' in ANSWER && ANSWER.token !== null).toBe(true);
        expect(ANSWER.token.length).toBe(JWT_TOKEN_LENGTH);
    });

    test("POST /auth.php avec nom d'utilisateur valable mais mot de passe invalable. Doit retourner l'erreur HTTP non autorisé", async () => {

        const QUERY = { login: "Alice", password: "mauvaisMotDePasse" };

        const response = await fetch(`${baseURL}/auth.php`, {
            method: 'POST', headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(QUERY)
        });
        expect(response.status).toBe(HTTP_UNAUTHORIZED);
    });

    test("GET /auth.php avec login/password valables. Doit retourner l'erreur HTTP invalide", async () => {

        const QUERY = { login: "Alice", password: "password1" };

        const response = await fetch(`${baseURL}/auth.php`); // GET by default
        expect(response.status).toBe(HTTP_METHOD_NOT_ALLOWED);
    });

});
