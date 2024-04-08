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

export type VacationType = {
    id: string;
    note: string;
    dateFrom: string;
    dateTo: string;
    requestedAt: string;
    requestedBy: EmployeeType;
    confirmed: boolean;
    rejected: boolean;
    reviewedAt: string;
    reviewedBy: EmployeeType;
}

export type CalendarDays = {
    [key: string]: VacationType[];
}

export type ReservedDayType = {
    id: string;
    reservedBy: EmployeeType;
    dateFrom: string;
    dateTo: string;
    note: string;
}