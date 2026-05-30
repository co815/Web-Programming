import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Grade } from '../models/course.model';

@Injectable({ providedIn: 'root' })
export class GradesService {
  constructor(private http: HttpClient) {}

  saveGrade(studentId: number, courseId: number, grade: number): Observable<{ ok: boolean }> {
    return this.http.post<{ ok: boolean }>(
      '/api/grades/save.php',
      { student_id: studentId, course_id: courseId, grade: grade.toString() },
      { withCredentials: true }
    );
  }

  deleteGrade(studentId: number, courseId: number): Observable<{ ok: boolean }> {
    return this.http.delete<{ ok: boolean }>(
      `/api/grades/delete.php?student_id=${studentId}&course_id=${courseId}`,
      { withCredentials: true }
    );
  }

  getMyGrades(): Observable<{ grades: Grade[] }> {
    return this.http.get<{ grades: Grade[] }>('/api/grades/mine.php', { withCredentials: true });
  }
}
