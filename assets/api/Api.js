import axios from 'axios';

const config = {
  headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
  timeout: 30000,
};

export class Api {
  static async authPost(url, data, configure) {
    return new Promise(resolve => {
      axios
        .post(url, data, { ...config, ...configure })
        .then(response => resolve(response))
        .catch(err => {
          if (err.response) {
            resolve(err.response);
          }
        });
    });
  }

  static async post(url, token, data, configure) {
    let authHeader = 'Bearer ' + token;
    let authorizedConfig = {
      headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*', 'Authorization': authHeader },
      timeout: 30000,
    };
    return new Promise(resolve => {
      axios
        .post(url, data, { ...authorizedConfig, ...configure })
        .then(response => resolve(response))
        .catch(err => {
          if (err.response) {
            resolve(err.response);
          }
        });
    });
  }

  static async get(url, token, params) {
    let authHeader = 'Bearer ' + token;
    let authorizedConfig = {
      headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*', 'Authorization': authHeader },
      timeout: 30000,
    };
    return new Promise(resolve => {
      axios
        .get(url, { ...authorizedConfig, params })
        .then(response => resolve(response.data))
        .catch(err => {
          if (err.response) {
            resolve(err.response);
          }
        });
    });
  }
}
