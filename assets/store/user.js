import { inject, reactive } from 'vue';
import { UserApi } from '../api';

export const userSymbol = Symbol('user');
export const useStateUser = () => inject(userSymbol);
export const createStateUser = () => {
  const state = reactive({
    user: null,
  });
  const fetchUser = async token => {
    const result = await UserApi.profile(token);
    if (result) {
      result.token = token;
      state.user = result;
      localStorage.token = token;
    } else {
      await logoutUser();
    }
    return result;
  };

  const logoutUser = async () => {
    state.user = null;
    localStorage.token = null;
  };

  const loginUser = async data => {
    const result = (await UserApi.auth(data)).data;
    if (result?.token) {
      await fetchUser(result?.token);
    }
    return result;
  };

  return { loginUser, fetchUser, logoutUser, state };
};

export const stateUser = createStateUser();
