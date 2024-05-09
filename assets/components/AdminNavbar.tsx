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
        <nav className="Navigation__AdminContainer">
            <div className="links-container">
                <img className="image" src="/vacated-logo-black.png" width={40} height={40}/> 
                <Link to="/admin" className="brand-name">
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
                                    <Link to="/admin">Dashboard</Link>
                                </li>
                                <li>
                                    <Link to="/admin/vacations">Vacations</Link>
                                </li>
                                <li>
                                    <Link to="/admin/employees">Employees</Link>
                                </li>
                                <li>
                                    <Link to="/admin/reserved-days">Reserved days</Link>
                                </li>
                                <li>
                                    <Link to="/admin/tags">Tags</Link>
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
                                <Button icon basic onClick={handleProfile} color="black" className="Icon__Button--noBorder">
                                    <Icon name='user' size="large"/>
                                </Button>
                            </li>
                            <li>
                                <Button icon basic onClick={handleLogout} color="black" className="Icon__Button--noBorder">
                                    <Icon name='sign-out' size="large"/> Log out
                                </Button>
                            </li>
                            {isAdmin && (
                                <li>
                                    <Link to="/">
                                        <Button color="black" className="admin-button">Exit admin dashboard</Button>
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