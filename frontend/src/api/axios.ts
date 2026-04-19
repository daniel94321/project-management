import axios, { type AxiosInstance, type InternalAxiosRequestConfig, type AxiosResponse, AxiosError } from 'axios'
import router from '@/router'

const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
  timeout: 30000,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Get XSRF token from cookie
    const token = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1]

    if (token) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token)
    }

    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor
apiClient.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // Redirect to login on unauthorized
          if (router.currentRoute.value.name !== 'login') {
            router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
          }
          break
        case 403:
          console.error('Forbidden: You do not have permission to perform this action.')
          break
        case 419:
          // CSRF token mismatch - refresh and retry
          await getCsrfCookie()
          return apiClient.request(error.config!)
        case 422:
          // Validation errors - let component handle
          break
        case 429:
          console.error('Too many requests. Please wait and try again.')
          break
        case 500:
          console.error('Server error. Please try again later.')
          break
      }
    }

    return Promise.reject(error)
  }
)

// Get CSRF cookie from Laravel
export async function getCsrfCookie(): Promise<void> {
  await axios.get(
    (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace('/api/v1', '') + '/sanctum/csrf-cookie',
    { withCredentials: true }
  )
}

export default apiClient
