export interface User {
  id: string
  name: string
  email: string
  status: 'active' | 'inactive' | 'suspended'
  email_verified_at: string | null
  last_login_at: string | null
  created_at: string
  updated_at: string
  roles: Role[]
  permissions: string[]
}

export interface Role {
  id: number
  name: string
  guard_name: string
  permissions: Permission[]
  created_at: string
  updated_at: string
}

export interface Permission {
  id: number
  name: string
  guard_name: string
}

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface LoginResponse {
  message: string
  user: User
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export interface PaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  per_page: number
  to: number | null
  total: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links?: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface UserFilters {
  page?: number
  per_page?: number
  search?: string
  status?: string
  role?: string
  sort_by?: string
  sort_direction?: 'asc' | 'desc'
}

export interface CreateUserPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  status?: 'active' | 'inactive' | 'suspended'
  roles?: string[]
}

export interface UpdateUserPayload {
  name?: string
  email?: string
  password?: string
  password_confirmation?: string
  status?: 'active' | 'inactive' | 'suspended'
  roles?: string[]
}

export interface Project {
  id: string
  name: string
  description: string | null
  status: 'planning' | 'active' | 'completed' | 'cancelled'
  priority: 'low' | 'medium' | 'high'
  start_date: string | null
  end_date: string | null
  owner: { id: string; name: string } | null
  created_at: string
  updated_at: string
}

export interface ProjectFilters {
  page?: number
  per_page?: number
  search?: string
  status?: string
  priority?: string
  sort_by?: string
  sort_direction?: 'asc' | 'desc'
}

export interface ProjectPayload {
  name: string
  description?: string
  status?: Project['status']
  priority?: Project['priority']
  start_date?: string
  end_date?: string
}
