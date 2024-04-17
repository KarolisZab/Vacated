import { Navigate } from 'react-router-dom'
import authService from '../services/auth-service'
import * as React from 'react';

interface PrivateRouteProps {
  children: React.ReactNode;
}

const PrivateRoute: React.FC<PrivateRouteProps> = ({ children }) => {
    const isAuthenticated = authService.isAuthenticated();

    return isAuthenticated ? <>{children}</> : <Navigate to="/login" />;
};

export default PrivateRoute;

export const AdminPrivateRoute: React.FC<PrivateRouteProps> = ({ children }) => {
    const isAuthenticated = authService.isAuthenticated();
    const isAdmin = authService.isAdmin();

    return isAuthenticated && isAdmin ? <>{children}</> : <Navigate to="/" />;
};