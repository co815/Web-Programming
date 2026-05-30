import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, map, tap } from 'rxjs';
import { User } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private userSubject = new BehaviorSubject<User | null>(null);
  currentUser$ = this.userSubject.asObservable();

  constructor(private http: HttpClient) {}

  login(username: string, password: string): Observable<User> {
    return this.http
      .post<{ user: User }>('/api/auth/login.php', { username, password }, { withCredentials: true })
      .pipe(tap(res => this.userSubject.next(res.user)), map(res => res.user));
  }

  register(data: {
    username: string;
    full_name: string;
    password: string;
    role: 'professor' | 'student';
    group_name: string;
  }): Observable<User> {
    return this.http
      .post<{ user: User }>('/api/auth/register.php', data, { withCredentials: true })
      .pipe(tap(res => this.userSubject.next(res.user)), map(res => res.user));
  }

  logout(): Observable<{ ok: boolean }> {
    return this.http
      .post<{ ok: boolean }>('/api/auth/logout.php', {}, { withCredentials: true })
      .pipe(tap(() => this.userSubject.next(null)));
  }

  loadCurrentUser(): Observable<User> {
    return this.http
      .get<{ user: User }>('/api/auth/me.php', { withCredentials: true })
      .pipe(tap(res => this.userSubject.next(res.user)), map(res => res.user));
  }

  get currentUser(): User | null {
    return this.userSubject.value;
  }
}
