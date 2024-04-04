/* eslint-disable */
import { useRouteError } from "react-router-dom";
import Navbar from './components/Navbar';
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

const ServerErrorPage: React.FC = () => {
  const error: any = useRouteError();

  if (errorCheck(error)) {
    return (
        <div>
            <Navbar />
            <div id="error-page">
                <h1>Oops! Something went wrong</h1>
                <p>Sorry, there was an internal server error.</p>
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

export default ServerErrorPage;
