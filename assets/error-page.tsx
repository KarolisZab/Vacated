/* eslint-disable */
import { useRouteError } from "react-router-dom";
import Navbar from './components/Navbar';
import AdminNavbar from './components/AdminNavbar';
import './styles/error-page.scss'

type ErrorResponse = {
  data: any;
  status: number;
  statusText: string;
  message?: string;
};

const errorCheck = (error: any): error is ErrorResponse => {
  return "data" in error && "status" in error && "statusText" in error;
};

export default function ErrorPage() {
  const error: any = useRouteError();

  if (errorCheck(error)) {
    return (
        <div>
            <Navbar />
            <div id="error-page">
                <h1>Oops! Page not found</h1>
                <p>Sorry the route you are looking for does not exist.</p>
                <p>
                  <i>{error.statusText || error.message}</i>
                </p>
            </div>
        </div>
    );
  } else {
    return <></>;
  }
}
