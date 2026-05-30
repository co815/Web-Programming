import { Component } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';

const GROUPS = ['1A', '1B', '2A', '2B', '3A', '3B'];

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [FormsModule, CommonModule, RouterLink],
  templateUrl: './register.component.html',
})
export class RegisterComponent {
  username = '';
  full_name = '';
  password = '';
  role: 'professor' | 'student' = 'student';
  group_name = '';
  errors: string[] = [];
  loading = false;
  readonly groups = GROUPS;

  constructor(private auth: AuthService, private router: Router) {}

  onRoleChange() {
    if (this.role !== 'student') this.group_name = '';
  }

  submit() {
    if (this.loading) return;
    this.errors = [];
    this.loading = true;
    this.auth
      .register({
        username: this.username,
        full_name: this.full_name,
        password: this.password,
        role: this.role,
        group_name: this.group_name,
      })
      .subscribe({
        next: user => {
          this.loading = false;
          this.router.navigate([user.role === 'professor' ? '/professor' : '/student']);
        },
        error: err => {
          this.loading = false;
          this.errors = err.error?.errors ?? ['Registration failed. Try again.'];
        },
      });
  }
}
