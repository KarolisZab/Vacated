import { useEffect, useState } from "react";
import { Link, NavLink } from "react-router-dom";
import "../styles/navbar.scss";
import authService from "../services/auth-service";

export default function Navbar() {
    const [isNavbarExpanded, setIsNavbarExpanded] = useState<boolean>(false);
    const [isAdmin, setIsAdmin] = useState<boolean>(false);
    const [isAuthenticated, setIsAuthenticated] = useState<boolean>(authService.isAuthenticated());
    const [user, setUser] = useState<any>(authService.getCurrentUser());

    useEffect(() => {
        const handleAuthenticationChange = () => {
            setIsAuthenticated(authService.isAuthenticated());
            setUser(authService.getCurrentUser());
        };

        authService.subscribe(handleAuthenticationChange);

        return () => {
            authService.unsubscribe(handleAuthenticationChange);
        };
    }, []);

    useEffect(() => {
        if (user) {
            setIsAdmin(user.roles.includes("ROLE_ADMIN"));
        } else {
            setIsAdmin(false);
        }
    }, [user]);

    const handleLogout = () => {
        authService.logout();
    };

    return (
        <nav className="navigation">
            <Link to="/home" className="brand-name">
                Vacated
            </Link>
            <button
                className="hamburger"
                onClick={() => {
                    setIsNavbarExpanded(!isNavbarExpanded);
                }}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="white"
                >
                    <path
                        fillRule="evenodd"
                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z"
                        clipRule="evenodd"
                    />
                </svg>
            </button>
            <div
                className={
                    isNavbarExpanded ? "navigation-menu expanded" : "navigation-menu"
                }
            >
                <ul>
                    {isAuthenticated && (
                        <>
                            <li>
                                <Link to="/home">Home</Link>
                            </li>
                            {isAdmin && (
                                <li>
                                    <Link to="/employees">Employees</Link>
                                </li>
                            )}
                            <li>
                                <Link to="/" onClick={handleLogout}>Logout</Link>
                            </li>
                        </>
                    )}
                </ul>
            </div>
        </nav>
    );
};