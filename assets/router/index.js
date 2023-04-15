import { appRoutes, loginRoute, registerRoute } from '../helpers/constants';
import { DismissalPage, EmployeePage, NotFoundPage, TurnoverPage, LoginPage, RegisterPage } from '../pages';
import { createRouter, createWebHistory } from 'vue-router';
import { notFoundRoute } from '../helpers/constants';

const routes = [
  {
    path: appRoutes.employee.path,
    name: appRoutes.employee.name,
    component: EmployeePage,
  },
  {
    path: appRoutes.dismissal.path,
    name: appRoutes.dismissal.name,
    component: DismissalPage,
  },
  {
    path: appRoutes.turnover.path,
    name: appRoutes.turnover.name,
    component: TurnoverPage,
  },
  {
    path: loginRoute.path,
    name: loginRoute.name,
    component: LoginPage,
  },
  {
    path: registerRoute.path,
    name: registerRoute.name,
    component: RegisterPage,
  },
  {
    path: notFoundRoute.path,
    name: notFoundRoute.name,
    component: NotFoundPage,
  },
];

const router = createRouter({
  history: createWebHistory('./'),
  routes,
});

export default router;
