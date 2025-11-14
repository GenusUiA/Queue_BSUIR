<?php

function loadScheduleForGroup(string $groupNumber, int $groupId, PDO $pdo): int
{
    // var url = ...
    $url = "https://iis.bsuir.by/api/v1/schedule?studentGroup={$groupNumber}";

    // var response = await _httpClient.GetAsync(url)
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return 0;
    }

    // var json = await response.Content.ReadAsStringAsync();
    $json = $response;

    // var scheduleData = JsonSerializer.Deserialize(...)
    $scheduleData = json_decode($json, true);
    if ($scheduleData === null)
        return 0;

    // Собираем все уроки
    // var lessons = new List<BsuirLessonDto>();
    $lessons = [];

    if (isset($scheduleData['schedules']) && is_array($scheduleData['schedules'])) {
        foreach ($scheduleData['schedules'] as $kv) {
            if ($kv !== null)
                $lessons = array_merge($lessons, $kv);
        }
    }

    // Фильтруем только ЛР и ПЗ
    $lessons = array_values(array_filter($lessons, function ($l) {
        return isset($l['lessonTypeAbbrev']) &&
            ($l['lessonTypeAbbrev'] == "ЛР" || $l['lessonTypeAbbrev'] == "ПЗ");
    }));

    if (count($lessons) === 0)
        return 0;

    // Подготавливаем словарь (date, subject) -> список подгрупп
    // var occurrences = new Dictionary<(string date, string subject), List<int>>();
    $occurrences = [];

    foreach ($lessons as $lesson) {

        if (empty($lesson['subject']))
            continue;

        $start = DateTime::createFromFormat("d.m.Y", $lesson['startLessonDate'] ?? "");
        if (!$start)
            continue;

        $end = DateTime::createFromFormat("d.m.Y", $lesson['endLessonDate'] ?? "");
        if (!$end)
            $end = clone $start;

        $subgroup = (($lesson['numSubgroup'] ?? 0) == 0) ? -1 : $lesson['numSubgroup'];

        // Добавляем все даты с шагом 4 недели
        for ($d = clone $start; $d <= $end; $d->modify("+28 days")) {

            $dateStr = $d->format("d.m.Y");
            $subject = $lesson['subject'];

            $key = $dateStr . "|" . $subject;

            if (!isset($occurrences[$key]))
                $occurrences[$key] = [];

            if (!in_array($subgroup, $occurrences[$key]))
                $occurrences[$key][] = $subgroup;
        }
    }

    // Получаем список всех предметов
    $subjectNames = [];
    foreach ($occurrences as $key => $_) {
        [, $subj] = explode("|", $key);
        $subjectNames[] = $subj;
    }
    $subjectNames = array_values(array_unique($subjectNames));

    // existingSubjects = await _context.Subjects.Where(...)
    if (count($subjectNames) > 0) {
        $in = implode(",", array_fill(0, count($subjectNames), "?"));
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE name IN ($in) AND group_id = ?");
        $stmt->execute(array_merge($subjectNames, [$groupId]));
        $existingSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $existingSubjects = [];
    }

    $subjectNameToId = [];
    foreach ($existingSubjects as $subj) {
        $subjectNameToId[$subj['name']] = $subj['id'];
    }
//
    // Добавляем новые предметы
    foreach ($subjectNames as $name) {
        if (!isset($subjectNameToId[$name])) {
            $stmt = $pdo->prepare("INSERT INTO subjects (name, group_id) VALUES (?, ?)");
            $stmt->execute([$name, $groupId]);
            $subjectNameToId[$name] = $pdo->lastInsertId();
        }
    }

    // existingDates = await _context.Dates.Where(...)
    $stmt = $pdo->prepare("SELECT subject_id, date, for_subgroup FROM dates WHERE group_id = ?");
    $stmt->execute([$groupId]);
    $existingDates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // existingDateSet = new HashSet<(int, string, int)>
    $existingDateSet = [];
    foreach ($existingDates as $d) {
        $existingDateSet[$d['subject_id'] . "|" . $d['date'] . "|" . $d['for_subgroup']] = true;
    }

    $addedCount = 0;

    foreach ($occurrences as $key => $subgroups) {

        [$date, $subjectName] = explode("|", $key);
        $subjectId = $subjectNameToId[$subjectName];

        if (count($subgroups) == 1) {

            $sub = $subgroups[0];
            $lookup = $subjectId . "|" . $date . "|" . $sub;

            if (!isset($existingDateSet[$lookup])) {

                $stmt = $pdo->prepare("
                    INSERT INTO dates (subject_id, date, for_subgroup, group_id)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$subjectId, $date, $sub, $groupId]);

                $existingDateSet[$lookup] = true;
                $addedCount++;
            }
        }
        else {

            for ($i = 0; $i < count($subgroups); $i++) {

                $sub = $subgroups[$i];
                $dateIndexed = $date . " (" . ($i + 1) . ")";

                $lookup = $subjectId . "|" . $dateIndexed . "|" . $sub;

                if (!isset($existingDateSet[$lookup])) {

                    $stmt = $pdo->prepare("
                        INSERT INTO dates (subject_id, date, for_subgroup, group_id)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$subjectId, $dateIndexed, $sub, $groupId]);

                    $existingDateSet[$lookup] = true;
                    $addedCount++;
                }
            }
        }
    }

    return $addedCount;
}
