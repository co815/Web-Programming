import { Component } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule, CommonModule, RouterLink],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  username = '';
  password = '';
  error = '';
  loading = false;

  constructor(private auth: AuthService, private router: Router) {}

  submit() {
    if (this.loading) return;
    this.error = '';
    this.loading = true;
    this.auth.login(this.username, this.password).subscribe({
      next: user => {
        this.loading = false;
        this.router.navigate([user.role === 'professor' ? '/professor' : '/student']);
      },
      error: () => {
        this.loading = false;
        this.error = 'Invalid username or password.';
      },
    });
  }
}
