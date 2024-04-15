import apiService from "./api-service";
import { ReservedDayType } from '../services/types';

const URL = '/admin/reserved-day';

class ReservedDayService {
    
    async getReservedDays(startDate: string, endDate: string): Promise<ReservedDayType[]> {
        const params = { startDate, endDate };
        return await apiService.get<ReservedDayType[]>('/reserved-day', params);
    }

    // async ReservedDay(vacationId: string): Promise<CalendarDays> {
    //     return await apiService.get<CalendarDays>(`${URL}/${vacationId}`);
    // }

    // Admin
    async reserveDays(reserveDayData: Partial<ReservedDayType>): Promise<ReservedDayType> {
        return await apiService.post<ReservedDayType>(`${URL}`, reserveDayData);
    }

    async updateReservedDays(reservedDayId: string, reserveDayData: Partial<ReservedDayType>): Promise<ReservedDayType> {
        return await apiService.patch<ReservedDayType>(`${URL}/${reservedDayId}`, reserveDayData);
    }

    async deleteReservedDay(reservedDayId: string): Promise<void> {
        return await apiService.delete(`${URL}/${reservedDayId}`);
    }

    async getReservedDaysCount(): Promise<number> {
        return await apiService.get<number>('/admin/all-reserved-count');
    }
}

export default new ReservedDayService();