import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import "../styles/navbar.scss";
import authService, { User } from "../services/auth-service";
import { Button, Icon } from "semantic-ui-react";

export default function Navbar() {
    const navigate = useNavigate();
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
        navigate('/login');
    };

    return (
        <nav className="navigation">
            <div className="links-container">
                <h1 className="brand-name">
                    Vacated
                </h1>
                <div
                    className={
                        isNavbarExpanded ? "navigation-menu expanded" : "navigation-menu"
                    }
                >
                    <ul>
                        {isAuthenticated && (
                            <>
                                <li>
                                    <Link to="/admin">Home</Link>
                                </li>
                                <li>
                                    {/* All user vacations */}
                                    <Link to="/admin/vacations">Vacations</Link>
                                </li>
                                <li>
                                    <Link to="/admin/employees">Employees</Link>
                                </li>
                                <li>
                                    <Link to="/admin/reserved-days">Reserved days</Link>
                                </li>
                            </>
                        )}
                    </ul>
                </div>
            </div>
            <div className="right-container">
                {isAuthenticated && (
                    <>
                        {isAdmin && (
                            <ul>
                                <li>
                                    <Link to="/">
                                        <Button basic color="teal">Exit admin dashboard</Button>
                                    </Link>
                                </li>
                            </ul>
                        )}
                        <ul>
                            <li>
                                {/* <Link to="/login" onClick={handleLogout}>Logout</Link> */}
                                <Button icon basic onClick={handleLogout} inverted>
                                    <Icon name='sign-out' size="large"/> {/* Use the 'log out' icon */}
                                </Button>
                            </li>
                        </ul>
                    </>
                )}
            </div>
            <button
                className="hamburger"
                onClick={() => {
                    setIsNavbarExpanded(!isNavbarExpanded);
                }}
            >
                <Icon name='bars' size="large" className="icon"/>
            </button>
        </nav>
    );
}