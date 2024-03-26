import { Outlet } from "react-router-dom";
import Navbar from "./Navbar";

export default function NavbarWrapper() {
    return (
        <div>
            <Navbar />
            <Outlet />
        </div>
    )
}