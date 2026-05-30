import { Routes } from '@angular/router';
import { professorGuard, studentGuard } from './guards/role.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  {
    path: 'login',
    loadComponent: () => import('./login/login.component').then(m => m.LoginComponent),
  },
  {
    path: 'register',
    loadComponent: () => import('./register/register.component').then(m => m.RegisterComponent),
  },
  {
    path: 'professor',
    canActivate: [professorGuard],
    loadComponent: () =>
      import('./professor/dashboard/dashboard.component').then(m => m.ProfessorDashboardComponent),
  },
  {
    path: 'professor/courses/:id/grades',
    canActivate: [professorGuard],
    loadComponent: () =>
      import('./professor/course-grades/course-grades.component').then(m => m.CourseGradesComponent),
  },
  {
    path: 'student',
    canActivate: [studentGuard],
    loadComponent: () =>
      import('./student/dashboard/dashboard.component').then(m => m.StudentDashboardComponent),
  },
  { path: '**', redirectTo: 'login' },
];
