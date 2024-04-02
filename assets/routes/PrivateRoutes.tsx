import { Navigate } from 'react-router-dom'
import authService from '../services/auth-service'

interface PrivateRouteProps {
  children: React.ReactNode;
}

const PrivateRoute: React.FC<PrivateRouteProps> = ({ children }) => {
  const isAuthenticated = authService.isAuthenticated();
  console.log('isAuthenticated', isAuthenticated);
  
  return isAuthenticated ? <>{children}</> : <Navigate to="/" />;
};

export default PrivateRoute;