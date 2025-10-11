import { router } from '@inertiajs/vue3';
import { startAuthentication, startRegistration } from '@simplewebauthn/browser';
import axios from 'axios';
import { ref } from 'vue';

export interface Passkey {
    id: string;
    name: string;
    device_name: string | null;
    device_type: string | null;
    browser_name: string | null;
    operating_system: string | null;
    last_used_at: string | null;
    created_at: string;
    is_recently_used: boolean;
}

export function usePasskeys() {
    const isRegistering = ref(false);
    const isAuthenticating = ref(false);
    const error = ref<string | null>(null);
    const browserSupported = ref(false);

    // Check browser support
    const checkBrowserSupport = () => {
        browserSupported.value =
            typeof PublicKeyCredential !== 'undefined' &&
            typeof PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable === 'function';
    };

    // Register a new passkey
    const registerPasskey = async (name?: string) => {
        if (!browserSupported.value) {
            error.value = 'Your browser does not support passkeys';
            return false;
        }

        isRegistering.value = true;
        error.value = null;

        try {
            // Step 1: Get registration options from server
            const optionsResponse = await axios.get(route('user.passkeys.generate-options'));
            const options = optionsResponse.data;

            // Step 2: Create credential using WebAuthn API
            const attestationResponse = await startRegistration(options);

            // Step 3: Send credential to server for storage
            const response = await axios.post(route('user.passkeys.store'), {
                passkey: JSON.stringify(attestationResponse),
                options: JSON.stringify(options),
                name: name || null,
            });

            if (response.data.success) {
                // Reload page to show new passkey
                router.reload({ only: ['passkeys'] });
                return true;
            } else {
                error.value = response.data.message || 'Failed to create passkey';
                return false;
            }
        } catch (err: any) {
            if (err.name === 'NotAllowedError') {
                error.value = 'Passkey creation was cancelled or not allowed';
            } else if (err.name === 'InvalidStateError') {
                error.value = 'A passkey already exists for this device';
            } else {
                error.value = err.message || 'An error occurred while creating the passkey';
            }
            console.error('Passkey registration error:', err);
            return false;
        } finally {
            isRegistering.value = false;
        }
    };

    // Authenticate with passkey
    const authenticateWithPasskey = async () => {
        if (!browserSupported.value) {
            error.value = 'Your browser does not support passkeys';
            return false;
        }

        isAuthenticating.value = true;
        error.value = null;

        try {
            // Step 1: Get authentication options from server
            const optionsResponse = await axios.get(route('passkey.authentication.options'));
            const options = optionsResponse.data;

            // Step 2: Authenticate using WebAuthn API
            const authenticationResponse = await startAuthentication(options);

            // Step 3: Verify authentication with server
            const response = await axios.post(route('passkey.authenticate'), {
                start_authentication_response: JSON.stringify(authenticationResponse),
            });

            if (response.data.success) {
                // Redirect to dashboard or specified URL
                window.location.href = response.data.redirect || route('dashboard');
                return true;
            } else {
                error.value = response.data.message || 'Authentication failed';
                return false;
            }
        } catch (err: any) {
            if (err.name === 'NotAllowedError') {
                error.value = 'Authentication was cancelled or not allowed';
            } else if (err.name === 'InvalidStateError') {
                error.value = 'No matching passkey found';
            } else {
                error.value = err.message || 'An error occurred during authentication';
            }
            console.error('Passkey authentication error:', err);
            return false;
        } finally {
            isAuthenticating.value = false;
        }
    };

    // Delete a passkey
    const deletePasskey = async (passkeyId: string) => {
        try {
            const response = await axios.delete(route('user.passkeys.destroy', { passkey: passkeyId }));

            if (response.data.success) {
                router.reload({ only: ['passkeys'] });
                return true;
            } else {
                error.value = response.data.message || 'Failed to delete passkey';
                return false;
            }
        } catch (err: any) {
            error.value = err.message || 'An error occurred while deleting the passkey';
            return false;
        }
    };

    // Get device icon based on device type
    const getDeviceIcon = (deviceType: string | null) => {
        switch (deviceType?.toLowerCase()) {
            case 'mobile':
                return 'smartphone';
            case 'tablet':
                return 'tablet';
            case 'desktop':
            default:
                return 'monitor';
        }
    };

    // Format last used time
    const formatLastUsed = (lastUsedAt: string | null) => {
        if (!lastUsedAt) return 'Never used';

        const date = new Date(lastUsedAt);
        const now = new Date();
        const diff = now.getTime() - date.getTime();
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;

        return date.toLocaleDateString();
    };

    // Initialize browser support check
    checkBrowserSupport();

    return {
        browserSupported,
        isRegistering,
        isAuthenticating,
        error,
        registerPasskey,
        authenticateWithPasskey,
        deletePasskey,
        getDeviceIcon,
        formatLastUsed,
    };
}