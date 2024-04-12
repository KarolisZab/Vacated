import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import "../styles/navbar.scss";
import authService, { User } from "../services/auth-service";
import { Button, Icon } from "semantic-ui-react";

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

    // const employee = 
    // <>
    //     <li>
    //         <Link to="/">Home</Link>
    //     </li>
    //     <li>
    //         <Link to="/vacations">My Vacations</Link>
    //     </li>
    // </>

    // const admin = 
    // <>
    //     <li>
    //         <Link to="/admin">Home</Link>
    //     </li>
    //     <li>
    //         {/* All user vacations */}
    //         <Link to="/admin/vacations">Vacations</Link>
    //     </li>
    //     <li>
    //         <Link to="/admin/employees">Employees</Link>
    //     </li>
    //     <li>
    //         <Link to="/admin/reservations">Reservations</Link>
    //     </li>
    // </>

    return (
        <nav className="navigation">
            <div className="links-container">
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
                                    <Link to="/">Home</Link>
                                </li>
                                <li>
                                    <Link to="/vacations">My Vacations</Link>
                                </li>
                            </>
                            // <>
                            //     {isAdmin ? (
                            //         admin
                            //     ) : (
                            //         employee
                            //     )}
                            // </>
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
                                    <Link to="/admin">
                                        <Button basic color="teal">Admin dashboard</Button>
                                    </Link>
                                </li>
                            </ul>
                        )}
                        <ul>
                            <li>
                                <Link to="/login" onClick={handleLogout}>Logout</Link>
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