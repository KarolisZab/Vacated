export const formatDateTime = (dateTimeString: string, includeTime: boolean = false) => {
    const date = new Date(dateTimeString);
    if (includeTime) {
        return date
            .toISOString()
            .split('T')[0];
    } else {
        return date
            .toISOString()
            .replace('T', ' ')
            .replace(/\..+/, '');
    }
};