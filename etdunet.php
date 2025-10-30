<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

$action = isset($_GET["action"]) ? $_GET["action"] : '0'; 

$edit_person = null;
$result = null;

switch ($action) {
    case '0': 
        $edit_person = getEditStudent($conn); 
        $result = getAllStudents($conn);  
        break;

    case '1': 
        deleteStudent($conn);
        $result = getAllStudents($conn);
        break;

    case '2': 
        updateStudent($conn);
        $result = getAllStudents($conn);
        break;

    case '3': 
    default:
        $result = getAllStudents($conn);
        break;

    case '4': 
        $edit_person = getEditStudent($conn);
        $result = getAllStudents($conn);
        break;
}

function addStudent($conn) {
    if (isset($_POST['add'])) {
        $nom = mysqli_real_escape_string($conn, $_POST['nom']);
        $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
        $age = intval($_POST['age']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        if (empty($nom) || empty($prenom)) {
            echo "<p style='color:red;'>Erreur : Nom et Prénom sont requis.</p>";
        } elseif ($age <= 0) {
            echo "<p style='color:red;'>Erreur : L'âge doit être positif.</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p style='color:red;'>Erreur : Email invalide.</p>";
        } else {
            $sql = "INSERT INTO Persons (Nom, Prenom, Age, Email) VALUES ('$nom', '$prenom', $age, '$email')";
            if (mysqli_query($conn, $sql)) {
                echo "<p style='color:green;'>Étudiant ajouté avec succès !</p>";
                header("Location: ?action=3"); 
                exit;
            } else {
                echo "<p style='color:red;'>Erreur : " . mysqli_error($conn) . "</p>";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    addStudent($conn);
    $result = getAllStudents($conn);
}

function deleteStudent($conn) {
    if (isset($_POST['delete']) && !empty($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $sql = "DELETE FROM Persons WHERE Email='$email'";
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>Supprimé avec succès</p>";
            header("Location: ?action=3");
            exit;
        } else {
            echo "<p style='color:red;'>Erreur : " . mysqli_error($conn) . "</p>";
        }
    }
}

function updateStudent($conn) {
    if (isset($_POST['update']) && !empty($_POST['original_email'])) {
        $original_email = mysqli_real_escape_string($conn, $_POST['original_email']);
        $nom = mysqli_real_escape_string($conn, $_POST['nom']);
        $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
        $age = intval($_POST['age']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "UPDATE Persons SET Nom='$nom', Prenom='$prenom', Age=$age, Email='$email' WHERE Email='$original_email'";
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>Mise à jour réussie !</p>";
            header("Location: ?action=3");
            exit;
        } else {
            echo "<p style='color:red;'>Erreur : " . mysqli_error($conn) . "</p>";
        }
    }
}

function getAllStudents($conn) {
    $sql = "SELECT Nom, Prenom, Age, Email FROM Persons";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        echo "<p style='color:red;'>Erreur SQL : " . mysqli_error($conn) . "</p>";
        return false;
    }
    return $result;
}

function getEditStudent($conn) {
    $edit_person = null;
    if (isset($_GET['edit_email'])) {
        $edit_email = mysqli_real_escape_string($conn, $_GET['edit_email']);
        $sql = "SELECT Nom, Prenom, Age, Email FROM Persons WHERE Email='$edit_email'";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $edit_person = mysqli_fetch_assoc($result);
        }
    }
    return $edit_person;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Étudiants</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 40px; }
        h2, h3 { text-align: center; color: #333; }
        table { border-collapse: collapse; width: 80%; margin: 20px auto; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .form-container { width: 50%; margin: 20px auto; padding: 20px; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="number"] {
            width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-size: 16px;
        }
        input[type="submit"]:hover { background-color: #0056b3; }
        .btn { display: inline-block; padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .actions { white-space: nowrap; }
        .add-button {
            display: block; width: 200px; margin: 20px auto; padding: 12px; background: #28a745; color: white;
 interesa: center; text-decoration: none; border-radius: 5px; font-size: 16px;
        }
        .add-button:hover { background: #218838; }
    </style>
</head>
<body>
    <h2>Gestion des Étudiants</h2>

    <a href="?action=0&add=true" class="add-button">Ajouter un étudiant</a>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <table>
            <tr>
                <th>Nom</th><th>Prénom</th><th>Âge</th><th>Email</th><th class="actions">Actions</th>
            </tr>
            <?php 
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["Nom"]); ?></td>
                    <td><?php echo htmlspecialchars($row["Prenom"]); ?></td>
                    <td><?php echo htmlspecialchars($row["Age"]); ?></td>
                    <td><?php echo htmlspecialchars($row["Email"]); ?></td>
                    <td class="actions">
                        <a href="?action=0&edit_email=<?php echo urlencode($row["Email"]); ?>" class="btn">Modifier</a>
                        <form action="?action=1" method="post" style="display:inline;" onsubmit="return confirm('Supprimer cet étudiant ?');">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($row["Email"]); ?>">
                            <input type="submit" name="delete" value="Supprimer" class="btn btn-danger" style="padding:8px 10px;font-size:12px;">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;color:#666;">Aucun étudiant trouvé.</p>
    <?php endif; ?>

    <?php if ($action == '0' || $edit_person): ?>
        <div class="form-container">
            <h3><?php echo $edit_person ? 'Modifier l’étudiant' : 'Ajouter un nouvel étudiant'; ?></h3>
            <form action="?action=<?php echo $edit_person ? '2' : '0'; ?>" method="post">
                <?php if ($edit_person): ?>
                    <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($edit_person['Email']); ?>">
                <?php endif; ?>
                <label>Nom:</label>
                <input type="text" name="nom" value="<?php echo $edit_person ? htmlspecialchars($edit_person['Nom']) : ''; ?>" required>
                <label>Prénom:</label>
                <input type="text" name="prenom" value="<?php echo $edit_person ? htmlspecialchars($edit_person['Prenom']) : ''; ?>" required>
                <label>Âge:</label>
                <input type="number" name="age" value="<?php echo $edit_person ? htmlspecialchars($edit_person['Age']) : ''; ?>" required min="1">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $edit_person ? htmlspecialchars($edit_person['Email']) : ''; ?>" required>
                <input type="submit" name="<?php echo $edit_person ? 'update' : 'add'; ?>" value="<?php echo $edit_person ? 'Mettre à jour' : 'Ajouter'; ?>">
            </form>
            <a href="?action=3" class="btn">Annuler</a>
        </div>
    <?php endif; ?>

<?php mysqli_close($conn); ?>
</body>
</html>