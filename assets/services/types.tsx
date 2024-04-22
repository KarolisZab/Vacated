export type EmployeeType = {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
    admin: boolean;
    tags: TagType[];
    availableDays: number;
};

export interface EmployeeRegistrationData extends Omit<EmployeeType, "id" | "roles" | "admin" | "availableDays"> {
}

export type EmployeesGetResultType = {
    totalItems: number;
    items: EmployeeType[];
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
    rejectionNote: string;
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
    tags: TagType[];
}

export type GetReservedDaysResultType = {
    totalItems: number;
    items: ReservedDayType[];
}

export type TagType = {
    id: string;
    name: string;
    colorCode: string;
}