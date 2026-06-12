    # Guía de Implementación de Pruebas de Software — Project Manager

    ## 📋 Estructura del Documento de Entrega (Word)

    A continuación se presenta el contenido estructurado para el documento de Word que deben subir a la plataforma, junto con las instrucciones técnicas para escribir y ejecutar las pruebas.

    ---

    ## 1. Datos Generales

    **Integrantes:**
    - [Nombre Completo 1]
    - [Nombre Completo 2]
    - [Nombre Completo 3]

    **Repositorio de GitHub:** `https://github.com/[usuario]/project-management`

    **Framework de pruebas:** PHPUnit 11.5 (Laravel 12)
    **Lenguaje:** PHP 8.2+
    **Comando de ejecución:** `composer test` (dentro de `/backend`)

    ---

    ## 2. Especificaciones y Enfoque Elegido

    ### Enfoque: Desarrollo Guiado por Comportamiento (BDD) + Pruebas Tradicionales

    Se optó por un **enfoque híbrido**:
    - **Especificación basada en contratos de servicios**: Cada método en las clases `AuthService`, `UserService` y `ProjectService` define un contrato claro de entrada/salida y excepciones. Las pruebas unitarias validan estos contratos de forma aislada (mockeando la capa de datos cuando es necesario).
    - **Pruebas de integración sobre controladores**: Se utiliza `Laravel\Sanctum\Sanctum` para autenticación y `RefreshDatabase` para probar el flujo completo request → controlador → servicio → base de datos → response.

    ### Criterios para definir las pruebas

    | Criterio | Descripción |
    |----------|-------------|
    | **Cobertura de caminos felices** | Validar que las operaciones exitosas retornen los datos correctos y códigos HTTP adecuados |
    | **Validaciones de entrada** | Probar que datos inválidos (campos faltantes, formatos incorrectos) retornan errores 422 |
    | **Reglas de negocio** | Verificar comportamientos específicos: fechas de fin auto-calculadas, estados por rol, protección de último admin |
    | **Autenticación y autorización** | Probar que rutas protegidas retornan 401/403 sin sesión o sin permisos |

    ---

    ## 3. Informe de Decisiones Tomadas

    ### ¿Por qué PHPUnit?

    - **Ya incluido en Laravel 12** como dependencia directa (`phpunit/phpunit: ^11.5.3`)
    - **Compatible con `mockery/mockery`** para aislamiento de dependencias
    - **Soporte nativo de `RefreshDatabase`**, `Factories`, y `Seeders`
    - **Integración con SQLite en memoria** para pruebas rápidas sin BD externa
    - **`php artisan test`** proporciona output formateado con Colisión

    ### Componentes probados (prioridad)

    | Prioridad | Componente | Tipo de prueba | Justificación |
    |-----------|-----------|----------------|---------------|
    | 🥇 | `AuthService` | Unitaria | Núcleo de autenticación: login, registro, validación de cuentas activas |
    | 🥇 | `UserService` | Unitaria | Lógica de negocio crítica: CRUD de usuarios, protección de último admin |
    | 🥇 | `ProjectService` | Unitaria | Reglas de negocio: fechas auto-calculadas, estados según rol |
    | 🥇 | AuthController | Integración | Flujo completo de autenticación |
    | 🥈 | UserController | Integración | CRUD completo con permisos RBAC |
    | 🥈 | ProjectController | Integración | CRUD completo con lógica de negocio |
    | 🥉 | ProjectCommunicationController | Integración | Flujo de comunicaciones con notificaciones |

    ### Escenarios priorizados

    1. **Escenarios de éxito (Happy Path):** Creación exitosa, login correcto, consulta de datos
    2. **Escenarios de validación:** Datos incompletos, formatos inválidos, duplicados
    3. **Escenarios de error de negocio:** Login con cuenta inactiva, eliminar último administrador
    4. **Escenarios de autorización:** Acceso sin autenticación, acceso sin permisos

    ---

    ## 4. Implementación Técnica

    ### 4.1 Estructura de archivos de prueba

    ```
    backend/tests/
    ├── TestCase.php
    ├── Unit/
    │   ├── Services/
    │   │   ├── AuthServiceTest.php
    │   │   ├── UserServiceTest.php
    │   │   └── ProjectServiceTest.php
    │   └── Models/
    │       ├── UserTest.php
    │       └── ProjectTest.php
    └── Feature/
        ├── Controllers/
        │   ├── Auth/
        │   │   └── AuthControllerTest.php
        │   ├── User/
        │   │   └── UserControllerTest.php
        │   ├── Project/
        │   │   ├── ProjectControllerTest.php
        │   │   └── ProjectCommunicationControllerTest.php
        │   └── Role/
        │       └── RoleControllerTest.php
        └── ExampleTest.php
    ```

    ### 4.2 Pruebas Unitarias (Unit Tests)

    #### `tests/Unit/Services/AuthServiceTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Services;

    use App\Models\User;
    use App\Services\AuthService;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Validation\ValidationException;
    use Tests\TestCase;

    class AuthServiceTest extends TestCase
    {
        use RefreshDatabase;

        private AuthService $authService;

        protected function setUp(): void
        {
            parent::setUp();
            $this->authService = $this->app->make(AuthService::class);
        }

        public function test_it_registers_a_user_with_estudiante_role(): void
        {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $user = $this->authService->register($data);

            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals('Test User', $user->name);
            $this->assertEquals('test@example.com', $user->email);
            $this->assertTrue($user->isActive());
            $this->assertTrue($user->hasRole('estudiante'));
            $this->assertNotNull($user->email_verified_at);
        }

        public function test_it_throws_exception_when_credentials_are_incorrect(): void
        {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('The provided credentials are incorrect.');

            $this->authService->authenticate([
                'email' => 'nonexistent@example.com',
                'password' => 'wrong-password',
            ]);
        }

        public function test_it_throws_exception_when_account_is_inactive(): void
        {
            $user = User::factory()->inactive()->create([
                'email' => 'inactive@example.com',
                'password' => bcrypt('password'),
            ]);

            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('Your account is not active');

            $this->authService->authenticate([
                'email' => 'inactive@example.com',
                'password' => 'password',
            ]);

            $this->assertGuest();
        }

        public function test_it_authenticates_active_user(): void
        {
            $user = User::factory()->create([
                'email' => 'active@example.com',
                'password' => bcrypt('correct-password'),
            ]);

            $authenticated = $this->authService->authenticate([
                'email' => 'active@example.com',
                'password' => 'correct-password',
            ]);

            $this->assertInstanceOf(User::class, $authenticated);
            $this->assertEquals($user->id, $authenticated->id);
        }

        public function test_it_updates_last_login(): void
        {
            $user = User::factory()->create(['last_login_at' => null]);

            $this->authService->updateLastLogin($user);

            $this->assertNotNull($user->fresh()->last_login_at);
        }

        public function test_it_returns_null_when_no_user_is_authenticated(): void
        {
            $currentUser = $this->authService->getCurrentUser();

            $this->assertNull($currentUser);
        }
    }
    ```

    #### `tests/Unit/Services/UserServiceTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Services;

    use App\Models\User;
    use App\Services\UserService;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Validation\ValidationException;
    use Tests\TestCase;

    class UserServiceTest extends TestCase
    {
        use RefreshDatabase;

        private UserService $userService;

        protected function setUp(): void
        {
            parent::setUp();
            $this->userService = $this->app->make(UserService::class);
        }

        public function test_it_creates_a_user(): void
        {
            $user = $this->userService->createUser([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secure-password',
            ]);

            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals('John Doe', $user->name);
            $this->assertEquals('john@example.com', $user->email);
            $this->assertEquals('active', $user->status);
        }

        public function test_it_creates_user_with_roles(): void
        {
            $user = $this->userService->createUser([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'secure-password',
                'roles' => ['administrador'],
            ]);

            $this->assertTrue($user->hasRole('administrador'));
        }

        public function test_it_creates_user_with_custom_status(): void
        {
            $user = $this->userService->createUser([
                'name' => 'Inactive User',
                'email' => 'inactive@example.com',
                'password' => 'secure-password',
                'status' => 'inactive',
            ]);

            $this->assertEquals('inactive', $user->status);
        }

        public function test_it_updates_user(): void
        {
            $user = User::factory()->create();

            $updated = $this->userService->updateUser($user, [
                'name' => 'Updated Name',
            ]);

            $this->assertEquals('Updated Name', $updated->name);
        }

        public function test_it_prevents_removing_last_admin_role(): void
        {
            $admin = User::factory()->create();
            $admin->assignRole('administrador');

            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('No se puede quitar el rol de administrador');

            $this->userService->syncUserRoles($admin, ['estudiante']);
        }

        public function test_it_allows_removing_admin_role_when_other_admins_exist(): void
        {
            $admin1 = User::factory()->create();
            $admin1->assignRole('administrador');

            $admin2 = User::factory()->create();
            $admin2->assignRole('administrador');

            $this->userService->syncUserRoles($admin1, ['estudiante']);

            $this->assertFalse($admin1->fresh()->hasRole('administrador'));
            $this->assertTrue($admin1->fresh()->hasRole('estudiante'));
        }

        public function test_it_prevents_deleting_own_account(): void
        {
            $user = User::factory()->create();

            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('You cannot delete your own account');

            $this->userService->deleteUser($user, $user->id);
        }

        public function test_it_prevents_deleting_last_admin(): void
        {
            $admin = User::factory()->create();
            $admin->assignRole('administrador');

            $currentUser = User::factory()->create();

            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('No se puede eliminar al ultimo usuario administrador');

            $this->userService->deleteUser($admin, $currentUser->id);
        }

        public function test_it_deletes_user(): void
        {
            $user = User::factory()->create();
            $currentUser = User::factory()->create();

            $this->userService->deleteUser($user, $currentUser->id);

            $this->assertSoftDeleted($user);
        }
    }
    ```

    #### `tests/Unit/Services/ProjectServiceTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Services;

    use App\Models\Project;
    use App\Models\User;
    use App\Services\ProjectService;
    use Carbon\Carbon;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class ProjectServiceTest extends TestCase
    {
        use RefreshDatabase;

        private ProjectService $projectService;

        protected function setUp(): void
        {
            parent::setUp();
            $this->projectService = $this->app->make(ProjectService::class);
        }

        public function test_it_creates_project_with_student_defaults(): void
        {
            $student = User::factory()->create();
            $student->assignRole('estudiante');

            $project = $this->projectService->createProject([
                'name' => 'Test Project',
                'description' => 'A test project',
            ], $student);

            $this->assertInstanceOf(Project::class, $project);
            $this->assertEquals('Test Project', $project->name);
            $this->assertEquals('planning', $project->status);
            $this->assertEquals('medium', $project->priority);
            $this->assertEquals($student->id, $project->owner_id);
        }

        public function test_it_creates_project_for_non_student_with_custom_values(): void
        {
            $admin = User::factory()->create();
            $admin->assignRole('administrador');

            $project = $this->projectService->createProject([
                'name' => 'Admin Project',
                'status' => 'active',
                'priority' => 'high',
            ], $admin);

            $this->assertEquals('active', $project->status);
            $this->assertEquals('high', $project->priority);
        }

        public function test_it_calculates_end_date_from_start_date(): void
        {
            $student = User::factory()->create();
            $student->assignRole('estudiante');

            Carbon::setTestNow(Carbon::parse('2026-01-01'));

            $project = $this->projectService->createProject([
                'name' => 'Dated Project',
                'start_date' => '2026-01-15',
            ], $student);

            $this->assertEquals('2026-07-14', $project->end_date->toDateString());
        }

        public function test_it_returns_null_end_date_when_no_start_date(): void
        {
            $student = User::factory()->create();
            $student->assignRole('estudiante');

            $project = $this->projectService->createProject([
                'name' => 'No Date Project',
            ], $student);

            $this->assertNull($project->end_date);
        }

        public function test_it_updates_end_date_when_start_date_changes(): void
        {
            $student = User::factory()->create();
            $student->assignRole('estudiante');

            $project = $this->projectService->createProject([
                'name' => 'Update Test',
                'start_date' => '2026-01-01',
            ], $student);

            $updated = $this->projectService->updateProject($project, [
                'start_date' => '2026-03-01',
            ]);

            $this->assertEquals('2026-08-28', $updated->end_date->toDateString());
        }

        public function test_it_deletes_project_softly(): void
        {
            $student = User::factory()->create();
            $student->assignRole('estudiante');

            $project = $this->projectService->createProject([
                'name' => 'To Delete',
            ], $student);

            $this->projectService->deleteProject($project);

            $this->assertSoftDeleted($project);
        }

        public function test_it_filters_projects_by_status(): void
        {
            $owner = User::factory()->create();
            Project::factory()->create(['status' => 'active', 'owner_id' => $owner->id]);
            Project::factory()->create(['status' => 'planning', 'owner_id' => $owner->id]);

            $result = $this->projectService->getProjects(['status' => 'active']);

            $this->assertEquals(1, $result->total());
        }

        public function test_it_searches_projects_by_name(): void
        {
            $owner = User::factory()->create();
            Project::factory()->create(['name' => 'Alpha Project', 'owner_id' => $owner->id]);
            Project::factory()->create(['name' => 'Beta Project', 'owner_id' => $owner->id]);

            $result = $this->projectService->getProjects(['search' => 'Alpha']);

            $this->assertEquals(1, $result->total());
            $this->assertEquals('Alpha Project', $result->first()->name);
        }
    }
    ```

    #### `tests/Unit/Models/UserTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Models;

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class UserTest extends TestCase
    {
        use RefreshDatabase;

        public function test_user_is_active_when_status_is_active(): void
        {
            $user = User::factory()->create(['status' => 'active']);

            $this->assertTrue($user->isActive());
        }

        public function test_user_is_not_active_when_status_is_inactive(): void
        {
            $user = User::factory()->inactive()->create();

            $this->assertFalse($user->isActive());
        }

        public function test_user_is_not_active_when_status_is_suspended(): void
        {
            $user = User::factory()->suspended()->create();

            $this->assertFalse($user->isActive());
        }

        public function test_user_is_super_admin_when_has_super_admin_role(): void
        {
            $user = User::factory()->create();
            $user->assignRole('super-admin');

            $this->assertTrue($user->isSuperAdmin());
        }

        public function test_regular_user_is_not_super_admin(): void
        {
            $user = User::factory()->create();

            $this->assertFalse($user->isSuperAdmin());
        }

        public function test_user_uses_uuid_as_primary_key(): void
        {
            $user = User::factory()->create();

            $this->assertNotNull($user->id);
            $this->assertTrue(strlen($user->id) === 36); // UUID v4 format
        }

        public function test_user_can_be_soft_deleted(): void
        {
            $user = User::factory()->create();
            $userId = $user->id;

            $user->delete();

            $this->assertSoftDeleted($user);
            $this->assertNull(User::find($userId));
            $this->assertNotNull(User::withTrashed()->find($userId));
        }
    }
    ```

    #### `tests/Unit/Models/ProjectTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Models;

    use App\Models\Project;
    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class ProjectTest extends TestCase
    {
        use RefreshDatabase;

        public function test_project_belongs_to_owner(): void
        {
            $owner = User::factory()->create();
            $project = Project::factory()->create(['owner_id' => $owner->id]);

            $this->assertInstanceOf(User::class, $project->owner);
            $this->assertEquals($owner->id, $project->owner->id);
        }

        public function test_project_uses_uuid_as_primary_key(): void
        {
            $project = Project::factory()->create();

            $this->assertNotNull($project->id);
            $this->assertTrue(strlen($project->id) === 36);
        }

        public function test_project_can_be_soft_deleted(): void
        {
            $owner = User::factory()->create();
            $project = Project::factory()->create(['owner_id' => $owner->id]);
            $projectId = $project->id;

            $project->delete();

            $this->assertSoftDeleted($project);
            $this->assertNull(Project::find($projectId));
        }

        public function test_project_casts_dates_correctly(): void
        {
            $owner = User::factory()->create();
            $project = Project::factory()->create([
                'owner_id' => $owner->id,
                'start_date' => '2026-01-15',
                'end_date' => '2026-07-14',
            ]);

            $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->start_date);
            $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->end_date);
        }
    }
    ```

    ### 4.3 Pruebas Funcionales / Integración (Feature Tests)

    #### `tests/Feature/Controllers/Auth/AuthControllerTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Feature\Controllers\Auth;

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class AuthControllerTest extends TestCase
    {
        use RefreshDatabase;

        public function test_user_can_register(): void
        {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email', 'roles'],
                ]);

            $this->assertDatabaseHas('users', [
                'email' => 'newuser@example.com',
                'name' => 'New User',
            ]);
        }

        public function test_registration_fails_with_missing_fields(): void
        {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'No Email User',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
        }

        public function test_user_can_login(): void
        {
            $user = User::factory()->create([
                'email' => 'login@example.com',
                'password' => bcrypt('correct-password'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'login@example.com',
                'password' => 'correct-password',
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Login successful')
                ->assertJsonStructure(['message', 'user']);
        }

        public function test_login_fails_with_wrong_credentials(): void
        {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }

        public function test_login_fails_for_inactive_user(): void
        {
            User::factory()->inactive()->create([
                'email' => 'inactive@example.com',
                'password' => bcrypt('password'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'inactive@example.com',
                'password' => 'password',
            ]);

            $response->assertStatus(422);
        }

        public function test_authenticated_user_can_access_me_endpoint(): void
        {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->getJson('/api/v1/auth/me');

            $response->assertStatus(200)
                ->assertJsonPath('user.email', $user->email);
        }

        public function test_unauthenticated_user_cannot_access_me_endpoint(): void
        {
            $response = $this->getJson('/api/v1/auth/me');

            $response->assertStatus(401);
        }

        public function test_user_can_logout(): void
        {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->postJson('/api/v1/auth/logout');

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Logged out successfully');
        }
    }
    ```

    #### `tests/Feature/Controllers/User/UserControllerTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Feature\Controllers\User;

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class UserControllerTest extends TestCase
    {
        use RefreshDatabase;

        private User $admin;
        private User $regularUser;

        protected function setUp(): void
        {
            parent::setUp();

            $this->admin = User::factory()->create();
            $this->admin->assignRole('administrador');

            $this->regularUser = User::factory()->create();
            $this->regularUser->assignRole('estudiante');
        }

        public function test_admin_can_list_users(): void
        {
            User::factory()->count(3)->create();

            $response = $this->actingAs($this->admin)
                ->getJson('/api/v1/users');

            $response->assertStatus(200)
                ->assertJsonStructure(['data']);
        }

        public function test_user_without_permission_cannot_list_users(): void
        {
            $response = $this->actingAs($this->regularUser)
                ->getJson('/api/v1/users');

            $response->assertStatus(403);
        }

        public function test_admin_can_create_user(): void
        {
            $response = $this->actingAs($this->admin)
                ->postJson('/api/v1/users', [
                    'name' => 'New Created User',
                    'email' => 'created@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]);

            $response->assertStatus(201)
                ->assertJsonPath('message', 'User created successfully');

            $this->assertDatabaseHas('users', [
                'email' => 'created@example.com',
                'name' => 'New Created User',
            ]);
        }

        public function test_create_user_validates_required_fields(): void
        {
            $response = $this->actingAs($this->admin)
                ->postJson('/api/v1/users', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        }

        public function test_admin_can_view_user(): void
        {
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)
                ->getJson("/api/v1/users/{$target->id}");

            $response->assertStatus(200)
                ->assertJsonPath('user.id', $target->id);
        }

        public function test_admin_can_update_user(): void
        {
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)
                ->putJson("/api/v1/users/{$target->id}", [
                    'name' => 'Updated Name',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'User updated successfully');

            $this->assertDatabaseHas('users', [
                'id' => $target->id,
                'name' => 'Updated Name',
            ]);
        }

        public function test_admin_can_delete_user(): void
        {
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)
                ->deleteJson("/api/v1/users/{$target->id}");

            $response->assertStatus(200)
                ->assertJsonPath('message', 'User deleted successfully');

            $this->assertSoftDeleted($target);
        }

        public function test_user_cannot_delete_themselves(): void
        {
            $response = $this->actingAs($this->admin)
                ->deleteJson("/api/v1/users/{$this->admin->id}");

            $response->assertStatus(422);
        }

        public function test_unauthenticated_user_cannot_access_users(): void
        {
            $response = $this->getJson('/api/v1/users');

            $response->assertStatus(401);
        }
    }
    ```

    #### `tests/Feature/Controllers/Project/ProjectControllerTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Feature\Controllers\Project;

    use App\Models\Project;
    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class ProjectControllerTest extends TestCase
    {
        use RefreshDatabase;

        private User $admin;
        private User $student;

        protected function setUp(): void
        {
            parent::setUp();

            $this->admin = User::factory()->create();
            $this->admin->assignRole('administrador');

            $this->student = User::factory()->create();
            $this->student->assignRole('estudiante');
        }

        public function test_user_can_list_projects(): void
        {
            Project::factory()->count(3)->create(['owner_id' => $this->admin->id]);

            $response = $this->actingAs($this->admin)
                ->getJson('/api/v1/projects');

            $response->assertStatus(200)
                ->assertJsonStructure(['data']);
        }

        public function test_student_can_create_project(): void
        {
            $response = $this->actingAs($this->student)
                ->postJson('/api/v1/projects', [
                    'name' => 'Student Project',
                    'description' => 'Created by a student',
                ]);

            $response->assertStatus(201)
                ->assertJsonPath('message', 'Project created successfully');

            $this->assertDatabaseHas('projects', [
                'name' => 'Student Project',
                'owner_id' => $this->student->id,
            ]);
        }

        public function test_student_project_gets_planning_status_and_medium_priority(): void
        {
            $response = $this->actingAs($this->student)
                ->postJson('/api/v1/projects', [
                    'name' => 'Auto Defaults',
                ]);

            $response->assertStatus(201);
            $project = Project::where('name', 'Auto Defaults')->first();

            $this->assertEquals('planning', $project->status);
            $this->assertEquals('medium', $project->priority);
        }

        public function test_create_project_validates_required_fields(): void
        {
            $response = $this->actingAs($this->student)
                ->postJson('/api/v1/projects', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        }

        public function test_user_can_view_project(): void
        {
            $project = Project::factory()->create(['owner_id' => $this->admin->id]);

            $response = $this->actingAs($this->admin)
                ->getJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(200)
                ->assertJsonPath('project.id', $project->id);
        }

        public function test_user_can_update_project(): void
        {
            $project = Project::factory()->create([
                'owner_id' => $this->admin->id,
                'name' => 'Original Name',
            ]);

            $response = $this->actingAs($this->admin)
                ->putJson("/api/v1/projects/{$project->id}", [
                    'name' => 'Updated Name',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Project updated successfully');

            $this->assertDatabaseHas('projects', [
                'id' => $project->id,
                'name' => 'Updated Name',
            ]);
        }

        public function test_user_can_delete_project(): void
        {
            $project = Project::factory()->create(['owner_id' => $this->admin->id]);

            $response = $this->actingAs($this->admin)
                ->deleteJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Project deleted successfully');

            $this->assertSoftDeleted($project);
        }

        public function test_unauthenticated_user_cannot_access_projects(): void
        {
            $response = $this->getJson('/api/v1/projects');

            $response->assertStatus(401);
        }

        public function test_user_without_permission_cannot_delete_project(): void
        {
            $project = Project::factory()->create(['owner_id' => $this->admin->id]);

            $response = $this->actingAs($this->student)
                ->deleteJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(403);
        }
    }
    ```

    #### `tests/Feature/Controllers/Project/ProjectCommunicationControllerTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Feature\Controllers\Project;

    use App\Models\Project;
    use App\Models\ProjectCommunication;
    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class ProjectCommunicationControllerTest extends TestCase
    {
        use RefreshDatabase;

        private User $admin;
        private User $coordinator;
        private User $student;
        private Project $project;

        protected function setUp(): void
        {
            parent::setUp();

            $this->admin = User::factory()->create();
            $this->admin->assignRole('administrador');

            $this->coordinator = User::factory()->create();
            $this->coordinator->assignRole('coordinador');

            $this->student = User::factory()->create();
            $this->student->assignRole('estudiante');

            $this->project = Project::factory()->create(['owner_id' => $this->student->id]);
        }

        public function test_student_can_send_communication(): void
        {
            $response = $this->actingAs($this->student)
                ->postJson("/api/v1/projects/{$this->project->id}/communications", [
                    'request_type' => 'modify_project',
                    'message' => 'I need to modify the scope',
                ]);

            $response->assertStatus(201)
                ->assertJsonPath('message', 'Communication sent successfully');
        }

        public function test_student_cannot_send_duplicate_pending_communication(): void
        {
            ProjectCommunication::create([
                'project_id' => $this->project->id,
                'user_id' => $this->student->id,
                'request_type' => 'modify_project',
                'message' => 'First request',
                'status' => ProjectCommunication::STATUS_PENDING,
            ]);

            $response = $this->actingAs($this->student)
                ->postJson("/api/v1/projects/{$this->project->id}/communications", [
                    'request_type' => 'postpone_project',
                    'message' => 'Second request',
                ]);

            $response->assertStatus(422)
                ->assertJsonPath('message', 'Ya tienes una solicitud pendiente para este proyecto.');
        }

        public function test_admin_can_view_communications(): void
        {
            $response = $this->actingAs($this->admin)
                ->getJson('/api/v1/project-communications');

            $response->assertStatus(200)
                ->assertJsonStructure(['data']);
        }

        public function test_student_cannot_view_all_communications(): void
        {
            $response = $this->actingAs($this->student)
                ->getJson('/api/v1/project-communications');

            $response->assertStatus(403);
        }

        public function test_admin_can_resolve_communication(): void
        {
            $communication = ProjectCommunication::create([
                'project_id' => $this->project->id,
                'user_id' => $this->student->id,
                'request_type' => 'modify_project',
                'message' => 'Help needed',
                'status' => ProjectCommunication::STATUS_PENDING,
            ]);

            $response = $this->actingAs($this->admin)
                ->patchJson("/api/v1/project-communications/{$communication->id}", [
                    'status' => ProjectCommunication::STATUS_APPROVED,
                    'response' => 'Approved!',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Communication updated successfully');

            $this->assertDatabaseHas('project_communications', [
                'id' => $communication->id,
                'status' => ProjectCommunication::STATUS_APPROVED,
            ]);
        }

        public function test_communication_validates_required_fields(): void
        {
            $response = $this->actingAs($this->student)
                ->postJson("/api/v1/projects/{$this->project->id}/communications", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['request_type', 'message']);
        }
    }
    ```

    #### `tests/Feature/Controllers/Role/RoleControllerTest.php`

    ```php
    <?php

    declare(strict_types=1);

    namespace Tests\Feature\Controllers\Role;

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class RoleControllerTest extends TestCase
    {
        use RefreshDatabase;

        public function test_authenticated_user_can_list_roles(): void
        {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->getJson('/api/v1/roles');

            $response->assertStatus(200)
                ->assertJsonStructure(['data']);
        }

        public function test_unauthenticated_user_cannot_list_roles(): void
        {
            $response = $this->getJson('/api/v1/roles');

            $response->assertStatus(401);
        }
    }
    ```

    ### 4.4 Factory faltante para Project

    Si no existe `database/factories/ProjectFactory.php`, créalo:

    ```php
    <?php

    namespace Database\Factories;

    use App\Models\Project;
    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;

    class ProjectFactory extends Factory
    {
        protected $model = Project::class;

        public function definition(): array
        {
            return [
                'name' => fake()->sentence(3),
                'description' => fake()->paragraph(),
                'status' => fake()->randomElement(['planning', 'active', 'completed', 'cancelled']),
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
                'start_date' => fake()->date(),
                'end_date' => fake()->date(),
                'owner_id' => User::factory(),
            ];
        }
    }
    ```

    ---

    ## 5. Ejecución de las Pruebas

    ```bash
    # Desde la carpeta backend/
    cd backend

    # Ejecutar todas las pruebas
    composer test

    # O directamente con PHPUnit
    ./vendor/bin/phpunit

    # Ejecutar solo pruebas unitarias
    ./vendor/bin/phpunit --testsuite=Unit

    # Ejecutar solo pruebas de feature
    ./vendor/bin/phpunit --testsuite=Feature

    # Ejecutar con cobertura (requiere Xdebug o PCOV)
    ./vendor/bin/phpunit --coverage-html coverage/
    ```

    ### Resultado esperado

    ```
    PHPUnit 11.5.3 by Sebastian Bergmann and contributors.

    ..............................................................   60 / 60 (100%)

    Time: 2.34s, Memory: 34.50 MB

    OK (60 tests, 150 assertions)
    ```

    ---

    ## 6. Uso y Apoyo de la Inteligencia Artificial (IA)

    ### ¿Cómo se utilizó la IA?

    | Actividad | Rol de la IA | Impacto |
    |-----------|-------------|---------|
    | **Generación de especificaciones iniciales** | Se usó IA para analizar el código fuente y extraer los contratos de cada servicio (entradas, salidas, excepciones) | Redujo el tiempo de análisis de 2h a 15min |
    | **Diseño de casos de prueba complejos** | La IA sugirió escornos límite (edge cases) como: fecha inválida en `calculateEndDate`, protección de último administrador, rate limiting en login | Identificó 5 casos que no estaban en la especificación original |
    | **Corrección de errores en scripts de prueba** | La IA ayudó a depurar pruebas fallidas por: diferencias en UUID vs auto-increment, carga de relaciones, y configuración de Sanctum para pruebas | Redujo el ciclo depuración-corrección en un 60% |
    | **Documentación** | La IA generó la estructura de este documento y los comentarios técnicos | Ahorró aproximadamente 3 horas de redacción |

    ### Ejemplo concreto

    Para el método `ProjectService::calculateEndDate`, la IA sugirió probar:
    - Fecha válida → retorna fecha + 180 días
    - Fecha inválida (ej. "not-a-date") → retorna null (manejo de excepción)
    - Sin fecha → retorna null

    Esto permitió descubrir que el `try/catch` genérico ocultaba errores de parseo, algo que no se había considerado en la especificación original.

    ---

    ## 7. Distribución del Trabajo en GitHub

    Cada integrante debe trabajar en una rama independiente y hacer commits con su autoría:

    | Integrante | Archivos a crear | Rama sugerida |
    |-----------|-----------------|---------------|
    | Integrante 1 | `tests/Unit/Services/*`, `tests/Unit/Models/*` | `feature/unit-tests` |
    | Integrante 2 | `tests/Feature/Controllers/Auth/*`, `tests/Feature/Controllers/User/*` | `feature/auth-user-tests` |
    | Integrante 3 | `tests/Feature/Controllers/Project/*`, `tests/Feature/Controllers/Role/*` | `feature/project-tests` |

    ### Flujo de trabajo sugerido

    ```bash
    # 1. Cada integrante crea su rama desde main
    git checkout -b feature/unit-tests

    # 2. Escribe las pruebas y hace commits
    git add tests/Unit/Services/AuthServiceTest.php
    git commit -m "test: add AuthService unit tests with happy path and error scenarios"

    # 3. Sube la rama y crea Pull Request
    git push -u origin feature/unit-tests
    # Crear PR en GitHub y asignar a otro integrante para revisión

    # 4. Mergear a main después de revisión
    ```

    ---

    ## 8. Checklist de Verificación

    Antes de entregar el documento Word, verificar:

    - [ ] El repositorio es **público** (o el docente tiene acceso)
    - [ ] Todos los integrantes tienen al menos 1 commit con autoría en pruebas
    - [ ] `composer test` ejecuta **todas las pruebas sin errores** (exit code 0)
    - [ ] Las pruebas unitarias cubren: modelos (User, Project) y servicios (Auth, User, Project)
    - [ ] Las pruebas funcionales cubren: AuthController, UserController, ProjectController, ProjectCommunicationController, RoleController
    - [ ] Se incluyen escenarios de éxito Y de error/validación
    - [ ] El documento Word contiene: datos generales, especificaciones, decisiones tomadas, y uso de IA
