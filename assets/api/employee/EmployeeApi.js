import { Api } from '../Api';

export class EmployeeApi extends Api {
  static async getEmployees(token, data) {
    return this.get(`/api/v1/employees/table`, token, data);
  }

  static async getCompanies(token, data) {
    return this.get(`/api/v1/employees/companies`, token, data);
  }

  static async getDepartments(token, data) {
    return this.get(`/api/v1/employees/departments`, token, data);
  }

  static async getPositions(token, data) {
    return this.get(`/api/v1/employees/positions`, token, data);
  }

  static async getCompetences(token, data) {
    return this.get(`/api/v1/employees/competences`, token, data);
  }

  static async getGrades(data) {
    return this.get(`/api/v1/employees/grades`, data);
  }
}
