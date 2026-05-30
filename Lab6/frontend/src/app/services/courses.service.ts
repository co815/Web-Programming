import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Course, StudentsPage } from '../models/course.model';

@Injectable({ providedIn: 'root' })
export class CoursesService {
  constructor(private http: HttpClient) {}

  getCourses(): Observable<{ courses: Course[] }> {
    return this.http.get<{ courses: Course[] }>('/api/courses/index.php', { withCredentials: true });
  }

  createCourse(name: string): Observable<{ course: Course }> {
    return this.http.post<{ course: Course }>('/api/courses/index.php', { name }, { withCredentials: true });
  }

  deleteCourse(id: number): Observable<{ ok: boolean }> {
    return this.http.delete<{ ok: boolean }>(`/api/courses/delete.php?id=${id}`, { withCredentials: true });
  }

  getStudents(courseId: number, group: string, page: number): Observable<StudentsPage> {
    return this.http.get<StudentsPage>(
      `/api/courses/students.php?course_id=${courseId}&group=${encodeURIComponent(group)}&page=${page}`,
      { withCredentials: true }
    );
  }
}
