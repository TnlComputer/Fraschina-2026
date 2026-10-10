<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DistribucionStockController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return
      view('pages.Distribucion.Stock.index');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // El módulo de stock todavía no está hecho: se muestra el aviso del listado
    return redirect()->route('distribucion_stock.index');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
