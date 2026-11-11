
CREATE TABLE `newsletter_receiver` (
  `id` int NOT NULL,
  `contact_id` int DEFAULT NULL,
  `newsletter_id` int DEFAULT NULL,
  `sent` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `newsletter_receiver`
--
ALTER TABLE `newsletter_receiver`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_receiver` (`contact_id`,`newsletter_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `newsletter_receiver`
--
ALTER TABLE `newsletter_receiver`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
