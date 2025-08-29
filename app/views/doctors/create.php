<!-- filepath: /Applications/XAMPP/xamppfiles/htdocs/spital-php/app/views/doctors/create.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Doctor</title>
    <script>
        // Specializările pentru fiecare departament
        const specializations = {
            "Cardiologie": ["Cardiologie intervențională", "Electrofiziologie", "Cardiologie pediatrică"],
            "Neurologie": ["Neurochirurgie", "Neurologie pediatrică", "Epileptologie"],
            "Chirurgie": ["Chirurgie generală", "Chirurgie plastică", "Chirurgie vasculară"],
            "Pediatrie": ["Neonatologie", "Pediatrie generală", "Pediatrie oncologică"],
            "Radiologie": ["Radiologie imagistică", "Radioterapie", "Radiologie intervențională"],
            "Oncologie": ["Oncologie medicală", "Hematologie oncologică", "Oncologie pediatrică"]
        };

        // Funcție pentru actualizarea specializărilor
        function updateSpecializations() {
            const department = document.getElementById("department").value;
            const specializationSelect = document.getElementById("specialization");

            // Golește lista de specializări
            specializationSelect.innerHTML = "";

            // Adaugă specializările corespunzătoare departamentului selectat
            if (specializations[department]) {
                specializations[department].forEach(specialization => {
                    const option = document.createElement("option");
                    option.value = specialization;
                    option.textContent = specialization;
                    specializationSelect.appendChild(option);
                });
            }
        }
    </script>
</head>
<body>
    <h1>Create a New Doctor</h1>
    <?php $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <form action="<?= $base ?>/index.php?r=spital/doctors/create" method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" maxlength="128" required>
        <br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" maxlength="128" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" maxlength="128" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" maxlength="128" required>
        <br>

        <label for="department">Department:</label>
        <select name="department" id="department" onchange="updateSpecializations()">
            <option value="Cardiologie">Cardiologie</option>
            <option value="Neurologie">Neurologie</option>
            <option value="Chirurgie">Chirurgie</option>
            <option value="Pediatrie">Pediatrie</option>
            <option value="Radiologie">Radiologie</option>
            <option value="Oncologie">Oncologie</option>
        </select>
        <br>

        <label for="specialization">Specialization:</label>
        <select name="specialization" id="specialization">
            <!-- Specializările vor fi populate dinamic -->
        </select>
        <br>

        <label for="grade">Grade:</label>
        <select name="grade" id="grade">
            <option value="Rezident">Rezident</option>
            <option value="Specialist">Specialist</option>
            <option value="Primar">Primar</option>
            <option value="Asistent universitar">Asistent universitar</option>
            <option value="Șef de secție">Șef de secție</option>
            <option value="Director medical">Director medical</option>
        </select>
        <br>

        <button type="submit">Create Doctor</button>
    </form>
</body>
</html>