-- Preserve the provider country selected for every new order.
ALTER TABLE active_number
    ADD COLUMN provider_country_name VARCHAR(100) NULL AFTER server_id;