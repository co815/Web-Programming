import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth.service';
import { GradesService } from '../../services/grades.service';
import { Grade } from '../../models/course.model';

@Component({
  selector: 'app-student-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class StudentDashboardComponent implements OnInit {
  grades: Grade[] = [];

  constructor(
    public auth: AuthService,
    private gradesService: GradesService,
    private router: Router
  ) {}

  ngOnInit() {
    this.gradesService.getMyGrades().subscribe({
      next: res => (this.grades = res.grades),
    });
  }

  logout() {
    this.auth.logout().subscribe(() => this.router.navigate(['/login']));
  }
}
