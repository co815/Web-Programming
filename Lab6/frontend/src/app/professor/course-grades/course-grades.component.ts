import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CoursesService } from '../../services/courses.service';
import { GradesService } from '../../services/grades.service';
import { Student, StudentsPage } from '../../models/course.model';

const GROUPS = ['1A', '1B', '2A', '2B', '3A', '3B'];

@Component({
  selector: 'app-course-grades',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './course-grades.component.html',
})
export class CourseGradesComponent implements OnInit {
  readonly groups = GROUPS;
  courseId = 0;
  selectedGroup = '';
  page = 1;
  studentsPage: StudentsPage | null = null;
  message = '';
  messageType: 'success' | 'error' = 'success';

  modalOpen = false;
  modalStudent: Student | null = null;
  modalGrade = '';
  isSubmitting = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private coursesService: CoursesService,
    private gradesService: GradesService
  ) {}

  ngOnInit() {
    this.courseId = Number(this.route.snapshot.paramMap.get('id'));
  }

  get totalPages(): number {
    if (!this.studentsPage) return 1;
    return Math.ceil(this.studentsPage.total / this.studentsPage.per_page);
  }

  loadStudents() {
    if (!this.selectedGroup) return;
    this.coursesService.getStudents(this.courseId, this.selectedGroup, this.page).subscribe({
      next: data => (this.studentsPage = data),
      error: () => this.showMessage('Failed to load students.', 'error'),
    });
  }

  onGroupChange() {
    this.page = 1;
    this.loadStudents();
  }

  prevPage() {
    if (this.page > 1) { this.page--; this.loadStudents(); }
  }

  nextPage() {
    if (this.page < this.totalPages) { this.page++; this.loadStudents(); }
  }

  openModal(student: Student) {
    this.modalStudent = student;
    this.modalGrade = student.grade !== null ? student.grade : '';
    this.modalOpen = true;
  }

  closeModal() {
    this.modalOpen = false;
    this.modalStudent = null;
    this.modalGrade = '';
  }

  saveGrade() {
    if (!this.modalStudent || this.isSubmitting) return;
    const g = parseFloat(this.modalGrade);
    if (isNaN(g) || g < 1 || g > 10) {
      this.showMessage('Grade must be between 1 and 10.', 'error');
      return;
    }
    this.isSubmitting = true;
    this.gradesService.saveGrade(this.modalStudent.id, this.courseId, g).subscribe({
      next: () => {
        this.isSubmitting = false;
        this.showMessage('Grade saved.', 'success');
        this.closeModal();
        this.loadStudents();
      },
      error: err => {
        this.isSubmitting = false;
        this.showMessage(err.error?.error ?? 'Failed to save grade.', 'error');
      },
    });
  }

  deleteGrade() {
    if (!this.modalStudent || this.isSubmitting || !confirm('Delete this grade?')) return;
    this.isSubmitting = true;
    this.gradesService.deleteGrade(this.modalStudent.id, this.courseId).subscribe({
      next: () => {
        this.isSubmitting = false;
        this.showMessage('Grade deleted.', 'success');
        this.closeModal();
        this.loadStudents();
      },
      error: () => {
        this.isSubmitting = false;
        this.showMessage('Failed to delete grade.', 'error');
      },
    });
  }

  private showMessage(text: string, type: 'success' | 'error') {
    this.message = text;
    this.messageType = type;
  }
}
