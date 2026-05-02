@extends('layouts.app')

@section('content')

<h2>Upload Invoice (AI)</h2>

<input type="file" id="image">
<button onclick="processInvoice()">Upload & Scan</button>

<hr>

<div id="result"></div>

<button onclick="saveData()">Confirm & Save</button>

<script>
let extractedData = {};

function processInvoice() {
    let formData = new FormData();
    formData.append('image', document.getElementById('image').files[0]);

    fetch('/ai/invoice/process', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(res => {
        extractedData = res.data;
        showData();
    });
}

function showData() {
    let html = `
    Party: <input id="party" value="${extractedData.party_name || ''}"><br>
    Bill No: <input id="bill_no" value="${extractedData.bill_no || ''}"><br>
    Date: <input id="date" value="${extractedData.date || ''}"><br>
    <hr>
    `;

    extractedData.items.forEach((item, i) => {
        html += `
        <div>
            Name: <input value="${item.name}">
            Bags: <input value="${item.bags}">
            Qty: <input value="${item.quantity}">
            Rate: <input value="${item.rate}">
            Amount: <input value="${item.amount}">
        </div>
        `;
    });

    document.getElementById('result').innerHTML = html;
}

function saveData() {
    extractedData.party_name = document.getElementById('party').value;
    extractedData.bill_no = document.getElementById('bill_no').value;
    extractedData.date = document.getElementById('date').value;

    fetch('/ai/invoice/save', {
        method: 'POST',
        body: JSON.stringify(extractedData),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(res => {
        alert("Saved Successfully");
    });
}
</script>

@endsection