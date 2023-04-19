import { appRoutes, loginRoute, registerRoute, notFoundRoute, errorRoute } from '../helpers/constants';
import {
  DismissalPage,
  EmployeePage,
  TurnoverPage,
  LoginPage,
  RegisterPage,
  HiringPage,
  NotFoundPage,
  ErrorPage,
} from '../pages';
import { createRouter, createWebHistory } from 'vue-router';
import { defineComponent } from 'vue';
const routes = [
  {
    path: appRoutes.employee.path,
    name: appRoutes.employee.name,
    component: defineComponent(EmployeePage),
  },
  {
    path: appRoutes.dismissal.path,
    name: appRoutes.dismissal.name,
    component: defineComponent(DismissalPage),
  },
  {
    path: appRoutes.turnover.path,
    name: appRoutes.turnover.name,
    component: defineComponent(TurnoverPage),
  },
  {
    path: appRoutes.hiring.path,
    name: appRoutes.hiring.name,
    component: defineComponent(HiringPage),
  },
  {
    path: loginRoute.path,
    name: loginRoute.name,
    component: defineComponent(LoginPage),
  },
  {
    path: registerRoute.path,
    name: registerRoute.name,
    component: defineComponent(RegisterPage),
  },
  {
    path: errorRoute.path,
    name: errorRoute.name,
    component: defineComponent(ErrorPage),
  },
  {
    path: notFoundRoute.path,
    name: notFoundRoute.name,
    component: defineComponent(NotFoundPage),
  },
];

const router = createRouter({
  history: createWebHistory('./'),
  routes,
});

export default router;
