import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, catchError, of } from 'rxjs';
import { AuthService } from '../services/auth.service';

export const professorGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  const check = (role: string | undefined) => {
    if (role === 'professor') return true;
    router.navigate([role === 'student' ? '/student' : '/login']);
    return false;
  };

  if (auth.currentUser) return of(check(auth.currentUser.role));

  return auth.loadCurrentUser().pipe(
    map(u => check(u.role)),
    catchError(() => { router.navigate(['/login']); return of(false); })
  );
};

export const studentGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  const check = (role: string | undefined) => {
    if (role === 'student') return true;
    router.navigate([role === 'professor' ? '/professor' : '/login']);
    return false;
  };

  if (auth.currentUser) return of(check(auth.currentUser.role));

  return auth.loadCurrentUser().pipe(
    map(u => check(u.role)),
    catchError(() => { router.navigate(['/login']); return of(false); })
  );
};
