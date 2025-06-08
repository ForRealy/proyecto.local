<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth_middleware.php';

// Protect the endpoint
requireAuth();

header('Content-Type: application/json');

try {
    // Get type distribution
    $typeQuery = "
        SELECT t.TypeName as type, COUNT(pt.PokemonNumber) as count
        FROM Types t
        LEFT JOIN PokemonTypes pt ON t.TypeID = pt.TypeID
        GROUP BY t.TypeName
        ORDER BY count DESC
    ";
    $typeStmt = $pdo->query($typeQuery);
    $typeDistribution = $typeStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get generation distribution (assuming Pokemon numbers 1-151 are Gen 1, 152-251 are Gen 2, etc.)
    $generationQuery = "
        SELECT 
            CASE 
                WHEN Number <= 151 THEN 'Generation 1'
                WHEN Number <= 251 THEN 'Generation 2'
                WHEN Number <= 386 THEN 'Generation 3'
                WHEN Number <= 493 THEN 'Generation 4'
                WHEN Number <= 649 THEN 'Generation 5'
                WHEN Number <= 721 THEN 'Generation 6'
                WHEN Number <= 809 THEN 'Generation 7'
                ELSE 'Generation 8'
            END as generation,
            COUNT(*) as count
        FROM Pokemon
        GROUP BY generation
        ORDER BY MIN(Number)
    ";
    $generationStmt = $pdo->query($generationQuery);
    $generationDistribution = $generationStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get type combinations
    $combinationQuery = "
        SELECT 
            GROUP_CONCAT(t.TypeName ORDER BY t.TypeName SEPARATOR '/') as combination,
            COUNT(DISTINCT p.Number) as count
        FROM Pokemon p
        JOIN PokemonTypes pt1 ON p.Number = pt1.PokemonNumber
        JOIN Types t ON pt1.TypeID = t.TypeID
        GROUP BY p.Number
        HAVING COUNT(DISTINCT t.TypeName) > 0
        ORDER BY count DESC
        LIMIT 10
    ";
    $combinationStmt = $pdo->query($combinationQuery);
    $typeCombinations = $combinationStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return all statistics
    echo json_encode([
        'typeDistribution' => $typeDistribution,
        'generationDistribution' => $generationDistribution,
        'typeCombinations' => $typeCombinations
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?> 