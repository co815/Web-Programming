import { Component, OnInit } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { CoursesService } from '../../services/courses.service';
import { Course } from '../../models/course.model';

@Component({
  selector: 'app-professor-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './dashboard.component.html',
})
export class ProfessorDashboardComponent implements OnInit {
  courses: Course[] = [];
  newCourseName = '';
  message = '';
  messageType: 'success' | 'error' = 'success';
  isSubmitting = false;
  deletingId: number | null = null;

  constructor(
    public auth: AuthService,
    private coursesService: CoursesService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadCourses();
  }

  loadCourses() {
    this.coursesService.getCourses().subscribe({
      next: res => (this.courses = res.courses),
      error: () => this.showMessage('Failed to load courses.', 'error'),
    });
  }

  addCourse() {
    if (this.isSubmitting) return;
    const name = this.newCourseName.trim();
    if (!name) return;
    this.isSubmitting = true;
    this.coursesService.createCourse(name).subscribe({
      next: res => {
        this.courses.push(res.course);
        this.isSubmitting = false;
        this.newCourseName = '';
        this.showMessage('Course created.', 'success');
      },
      error: err => {
        this.isSubmitting = false;
        this.showMessage(err.error?.error ?? 'Failed to create course.', 'error');
      },
    });
  }

  deleteCourse(course: Course) {
    if (!confirm(`Delete course "${course.name}" and all its grades?`)) return;
    this.deletingId = course.id;
    this.coursesService.deleteCourse(course.id).subscribe({
      next: () => {
        this.deletingId = null;
        this.courses = this.courses.filter(c => c.id !== course.id);
        this.showMessage('Course deleted.', 'success');
      },
      error: () => {
        this.deletingId = null;
        this.showMessage('Failed to delete course.', 'error');
      },
    });
  }

  logout() {
    this.auth.logout().subscribe(() => this.router.navigate(['/login']));
  }

  private showMessage(text: string, type: 'success' | 'error') {
    this.message = text;
    this.messageType = type;
  }
}
