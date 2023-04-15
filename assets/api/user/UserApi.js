import { Api } from '../Api';
export class UserApi extends Api {
  static async auth(data) {
    return this.authPost(`/api/v1/users/auth`, data);
  }

  static async profile(token, data) {
    return this.get(`/api/v1/users/profile`, token, data);
  }

  static async register(token, data) {
    return this.post(`/api/v1/users/register`, token, data);
  }
}