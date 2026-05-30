export interface Course {
  id: number;
  name: string;
}

export interface Student {
  id: number;
  full_name: string;
  grade: string | null;
}

export interface StudentsPage {
  students: Student[];
  total: number;
  page: number;
  per_page: number;
}

export interface Grade {
  course_name: string;
  professor_name: string;
  grade: string;
}
