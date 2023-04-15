import { Api } from '../Api';

export class AnalyticsApi extends Api {
  static async getDismissal(token, data) {
    return this.get(`/api/v1/analytics/dismissal`, token, data);
  }

  static async getTurnover(token, data) {
    return this.get(`/api/v1/analytics/turnover`, token, data);
  }
}
