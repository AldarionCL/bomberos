import axios from 'axios'

const client = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        Accept: 'application/json',
    },
})

export async function ensureCsrfCookie() {
    await axios.get('/sanctum/csrf-cookie', {withCredentials: true})
}

export default client
