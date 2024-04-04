export type EmployeeType = {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
};

export interface EmployeeRegistrationData extends Omit<EmployeeType, "id" | "roles"> {
    password: string;
    confirmPassword: string;
}