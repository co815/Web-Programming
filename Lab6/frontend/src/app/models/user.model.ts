export interface User {
  id: number;
  username: string;
  full_name: string;
  role: 'professor' | 'student';
  group_name: string | null;
}
