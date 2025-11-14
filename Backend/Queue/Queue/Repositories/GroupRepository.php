<?php

class GroupRepository
{
    private PDO $db;
//
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // === FetchGroupsFromBsuirAsync ===
    public function fetchGroupsFromBsuir(): array
    {
        $url = "https://iis.bsuir.by/api/v1/student-groups";

        $json = file_get_contents($url);
        if (!$json) return [];

        $groups = json_decode($json, true);

        if (!$groups) return [];

        // Приводим к модели Group
        $entities = [];
        foreach ($groups as $g) {
            $entities[] = [
                "Name" => $g["name"]
            ];
        }

        return $entities;
    }

    // === SaveGroupsToDatabaseAsync ===
    public function saveGroupsToDatabase(): int
    {
        $groups = $this->fetchGroupsFromBsuir();

        // Получаем существующие названия
        $stmt = $this->db->query("SELECT Name FROM groups");
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // фильтруем новые
        $newGroups = array_filter($groups, function($g) use ($existing) {
            return !in_array($g["Name"], $existing, true);
        });

        if (empty($newGroups)) return 0;

        $stmt = $this->db->prepare("INSERT INTO groups (Name) VALUES (?)");

        foreach ($newGroups as $g) {
            $stmt->execute([$g["Name"]]);
        }

        return count($newGroups);
    }

    // === GetAllGroupsAsync ===
    public function getAllGroups(): array
    {
        $stmt = $this->db->query("SELECT * FROM groups ORDER BY Name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
