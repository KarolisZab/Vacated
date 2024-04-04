import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import "../styles/navbar.scss";
import authService, { User } from "../services/auth-service";
import { Icon } from "semantic-ui-react";

export default function Navbar() {
    const [isNavbarExpanded, setIsNavbarExpanded] = useState<boolean>(false);
    const [isAdmin, setIsAdmin] = useState<boolean>(false);
    const [isAuthenticated, setIsAuthenticated] = useState<boolean>(authService.isAuthenticated());
    const [user, setUser] = useState<User | null>(authService.getCurrentUser());

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
            <Link to="/" className="brand-name">
                Vacated
            </Link>
            <button
                className="hamburger"
                onClick={() => {
                    setIsNavbarExpanded(!isNavbarExpanded);
                }}
            >
                <Icon name='bars' size="large" className="icon"/>
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
                                <Link to="/">Home</Link>
                            </li>
                            {isAdmin && (
                                <li>
                                    <Link to="/employees">Employees</Link>
                                </li>
                            )}
                            <li>
                                <Link to="/login" onClick={handleLogout}>Logout</Link>
                            </li>
                        </>
                    )}
                </ul>
            </div>
        </nav>
    );
}