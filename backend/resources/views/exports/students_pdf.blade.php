<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
    <style>
         body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #FFA500; /* Orange */
            color: #fff;
            padding: 10px;
            text-align: center;
            display: flex;
            align-items: center;
        }

        .header img {
            height: 40px;
            margin-right: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .title {
            text-align: center;
            margin: 20px 0 10px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            word-break: break-word;
            white-space: normal;
        }

        /* Adjusted column widths */
        th:nth-child(1), td:nth-child(1) { width: 12%; } /* First Name */
        th:nth-child(2), td:nth-child(2) { width: 12%; } /* Last Name */
        th:nth-child(3), td:nth-child(3) { width: 6%; }  /* Age */
        th:nth-child(4), td:nth-child(4) { width: 10%; } /* Gender */
        th:nth-child(5), td:nth-child(5) { width: 18%; } /* Address */
        th:nth-child(6), td:nth-child(6) { width: 14%; } /* Contact Number */
        th:nth-child(7), td:nth-child(7) { width: 12%; } /* Course */
        th:nth-child(8), td:nth-child(8) { width: 20%; } /* Email */
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/default_avatar.jpg') }}">
        <h1>Task Submission System</h1>
    </div>

    <div class="title"><strong>Students List</strong></div>

    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Course</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->age }}</td>
                <td>{{ $student->gender }}</td>
                <td>{{ $student->address }}</td>
                <td>{{ $student->contact_number }}</td>
                <td>{{ $student->course }}</td>
                <td>{{ $student->user ? $student->user->email : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
