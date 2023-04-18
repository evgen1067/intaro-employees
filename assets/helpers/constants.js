export const appRoutes = {
  employee: {
    path: '/',
    name: 'employee',
    title: 'Сотрудники',
    meta: {
      icon: 'badge',
    },
  },
  dismissal: {
    path: '/dismissal',
    name: 'dismissal',
    title: 'Увольнения',
    meta: {
      icon: 'insights',
    },
  },
  turnover: {
    path: '/turnover',
    name: 'turnover',
    title: 'Текучесть',
    meta: {
      icon: 'bar_chart',
    },
  },
  hiring: {
    path: '/hiring',
    name: 'hiring',
    title: 'Планы по найму',
    meta: {
      icon: 'people_alt',
    },
  },
};

export const notFoundRoute = {
  path: '/:catchAll(.*)',
  name: 'notfound',
};

export const loginRoute = {
  path: '/login',
  name: 'login',
};

export const registerRoute = {
  path: '/register',
  name: 'register',
};
