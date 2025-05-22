USE dapur;

-- Add order_status column if not exists
ALTER TABLE placed_orders 
ADD COLUMN IF NOT EXISTS order_status ENUM('Menunggu', 'Sedang Diproses', 'Terkirim', 'Dibatalkan') DEFAULT 'Menunggu';

-- Update existing orders based on delivered and canceled columns
UPDATE placed_orders 
SET order_status = 
    CASE 
        WHEN canceled = 1 THEN 'Dibatalkan'
        WHEN delivered = 1 THEN 'Terkirim'
        ELSE 'Menunggu'
    END
WHERE order_status IS NULL; 