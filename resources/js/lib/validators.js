import { z } from 'zod';

export const storeImportSchema = z.object({
    marketplace_id: z.coerce.number().int().positive(),
    file: z
        .instanceof(File, { message: 'A file is required.' })
        .refine((file) => ['.csv', '.xlsx', '.xls'].some((ext) => file.name.toLowerCase().endsWith(ext)), {
            message: 'File must be a CSV, XLSX, or XLS.',
        }),
});

export const sellerSchema = z.object({
    name: z.string().min(1, 'Name is required').max(255),
    trade_name: z.string().max(255).nullable().optional(),
    email: z.string().email().nullable().optional(),
    address_street: z.string().max(255).nullable().optional(),
    address_number: z.string().max(20).nullable().optional(),
    address_complement: z.string().max(100).nullable().optional(),
    address_district: z.string().max(100).nullable().optional(),
    address_city: z.string().max(100).nullable().optional(),
    address_state: z.string().length(2).nullable().optional(),
    address_zip: z.string().max(10).nullable().optional(),
});
