import { Api } from '../Api';

export class HiringApi extends Api {
  static async getList(token, data) {
    return this.get(`/api/v1/hiring/list`, token, data);
  }

  static async getManagers(token, data) {
    return this.get(`/api/v1/hiring/managers`, token, data);
  }

  static async addRecord(token, data) {
    return this.post(`/api/v1/hiring/new`, token, data);
  }

  static async getRecord(token, id, data) {
    return this.get(`/api/v1/hiring/${id}`, token, data);
  }

  static async editRecord(token, id, data) {
    return this.post(`/api/v1/hiring/edit/${id}`, token, data);
  }

  static async deleteRecords(token, data) {
    return this.post(`/api/v1/hiring/delete`, token, data);
  }
}
