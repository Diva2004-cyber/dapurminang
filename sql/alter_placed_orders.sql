USE dapur;

-- Add payment_proof column if not exists
ALTER TABLE placed_orders 
ADD COLUMN IF NOT EXISTS payment_proof VARCHAR(255) NULL;

-- Add payment_status column if not exists
ALTER TABLE placed_orders 
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'menunggu-verifikasi', 'sukses', 'ditolak') DEFAULT 'pending';

-- Add payment_notes column if not exists
ALTER TABLE placed_orders 
ADD COLUMN IF NOT EXISTS payment_notes TEXT NULL;

-- Add order_status column if not exists
ALTER TABLE placed_orders 
ADD COLUMN IF NOT EXISTS order_status ENUM('Menunggu', 'Sedang Diproses', 'Terkirim', 'Dibatalkan') DEFAULT 'Menunggu';

-- Modify payment_status column to support required enum values
ALTER TABLE placed_orders 
MODIFY COLUMN payment_status ENUM('pending', 'menunggu-verifikasi', 'sukses', 'ditolak') DEFAULT 'pending'; 