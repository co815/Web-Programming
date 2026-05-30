import { TestBed } from '@angular/core/testing';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { AuthService } from './auth.service';
import { firstValueFrom } from 'rxjs';

describe('AuthService', () => {
  let service: AuthService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        AuthService,
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });
    service = TestBed.inject(AuthService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('login sets currentUser$', async () => {
    const loginPromise = firstValueFrom(service.login('alice', 'secret'));

    const req = http.expectOne('/api/auth/login.php');
    expect(req.request.method).toBe('POST');
    req.flush({ user: { id: 1, username: 'alice', full_name: 'Alice', role: 'student', group_name: '1A' } });

    const user = await loginPromise;
    expect(user.username).toBe('alice');

    const currentUser = await firstValueFrom(service.currentUser$);
    expect(currentUser?.username).toBe('alice');
  });

  it('logout clears currentUser$', async () => {
    const logoutPromise = firstValueFrom(service.logout());

    http.expectOne('/api/auth/logout.php').flush({ ok: true });

    await logoutPromise;

    const currentUser = await firstValueFrom(service.currentUser$);
    expect(currentUser).toBeNull();
  });
});
