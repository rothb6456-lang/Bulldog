const CONFIG = {
    API_BASE_URL: 'http://localhost:8000/api/v1/training',
    AUTH_TOKEN_KEY: 'momentum_sanctum_token'
};

const PENDING_SESSIONS_KEY = 'momentum_pending_sessions';

function getAuthToken() {
    return localStorage.getItem(CONFIG.AUTH_TOKEN_KEY);
}

function isAuthenticated() {
    return Boolean(getAuthToken());
}

function getPendingSessions() {
    try {
        return JSON.parse(localStorage.getItem(PENDING_SESSIONS_KEY) || '[]');
    } catch {
        return [];
    }
}

function queueSession(session) {
    const pending = getPendingSessions();
    pending.push({ ...session, queued_at: new Date().toISOString() });
    localStorage.setItem(PENDING_SESSIONS_KEY, JSON.stringify(pending));
    return pending.length;
}

async function requestCloud(path, options = {}) {
    const token = getAuthToken();
    if (!token) {
        throw new Error('Authentication is required for cloud actions.');
    }

    const response = await fetch(`${CONFIG.API_BASE_URL}${path}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`,
            ...(options.headers || {})
        }
    });

    if (!response.ok) {
        throw new Error(`Cloud request failed with status ${response.status}.`);
    }

    return response.json();
}

async function syncPendingSessions() {
    if (!isAuthenticated() || !navigator.onLine) {
        return { synced: 0, remaining: getPendingSessions().length };
    }

    const pending = getPendingSessions();
    let synced = 0;

    while (pending.length > 0) {
        await requestCloud('/sessions', {
            method: 'POST',
            body: JSON.stringify(pending[0])
        });
        pending.shift();
        synced += 1;
        localStorage.setItem(PENDING_SESSIONS_KEY, JSON.stringify(pending));
    }

    return { synced, remaining: pending.length };
}

window.MomentumSync = {
    CONFIG,
    getAuthToken,
    isAuthenticated,
    getPendingSessions,
    queueSession,
    requestCloud,
    syncPendingSessions
};

window.addEventListener('online', () => {
    syncPendingSessions().catch(() => { });
});
