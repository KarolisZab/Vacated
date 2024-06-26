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

    const handleProfile =() => {
        navigate('/profile');
    }

    return (
        <nav className="navigation">
            <div className="links-container">
                <img className="image" src="/vacated-logo-white.png" width={40} height={40}/> 
                <Link to="/" className="brand-name">
                    Vacated
                </Link>
                <div
                    className={
                        isNavbarExpanded ? "navigation-menu expanded" : "navigation-menu"
                    }
                >
                    <ul>
                        {isAuthenticated && (
                            <>
                                <li>
                                    <Link to="/">Calendar</Link>
                                </li>
                                <li>
                                    <Link to="/vacations">My Vacations</Link>
                                </li>
                                <li>
                                    <Link to="/reserved-days">Reserved days</Link>
                                </li>
                            </>
                        )}
                    </ul>
                </div>
            </div>
            <div className="right-container">
                {isAuthenticated && (
                    <>
                        <ul className="button-group">
                            <li>
                                <Button icon basic onClick={handleProfile} inverted>
                                    <Icon name='user' size="large"/>
                                </Button>
                            </li>
                            <li>
                                <Button icon basic onClick={handleLogout} inverted className="logout-button">
                                    <Icon name='sign-out' size="large"/> Log out
                                </Button>
                            </li>
                            {isAdmin && (
                                <li>
                                    <Link to="/admin">
                                        <Button color="teal" className="admin-button">Admin dashboard</Button>
                                    </Link>
                                </li>
                            )}
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