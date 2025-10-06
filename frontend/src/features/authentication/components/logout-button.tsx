import { logoutAction } from '../actions/auth-actions';

export default function LogoutButton() {
    return (
        <form action={logoutAction}>
            <button
                type="submit"
                className="text-red-600 hover:text-red-500 font-medium"
            >
                Logout
            </button>
        </form>
    );
}