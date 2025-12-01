<?php

namespace App\Services;

use App\Enums\FileType;
use App\Repositories\Interfaces\FileRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileService
{
  private $repository;
  
  public function __construct(FileRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function storeShapefile($file)
  { 
      $randomName = Str::random(20);
      $extension = $file->getClientOriginalExtension();
      $fileName = "{$randomName}.{$extension}";

      $path = $file->storeAs('shapefiles', $fileName);

      // Salva os dados do arquivo na tabela 'files'
      return $this->repository->store([
          'type' => FileType::SHAPEFILE_ZIP->value, 
          'name' => $fileName,
          'path' => $path, 
      ]);
  }

  public function makeGeoJson($shapefilePath)
  {
      $randomName = Str::random(20) . '.geojson';
      $geoJsonPath = "geojsons/{$randomName}";

      // Caminhos completos para o shapefile e o GeoJSON
      $shapefileFullPath = storage_path("app/{$shapefilePath}");
      $geoJsonFullPath = storage_path("app/{$geoJsonPath}");

      // Comando ogr2ogr para converter o shapefile
      $command = "ogr2ogr -f GeoJSON -t_srs EPSG:4326 {$geoJsonFullPath} /vsizip/{$shapefileFullPath}";
      $output = shell_exec($command . ' 2>&1'); // Captura erros do comando

      // Verifica se o arquivo GeoJSON foi criado com sucesso
      if (!file_exists($geoJsonFullPath)) {
          throw new \Exception("Failed to convert shapefile to GeoJSON: {$output}");
      }

      // Salva o GeoJSON na tabela 'files'
      return $this->repository->store([
          'type' => FileType::GEOJSON->value,
          'name' => $randomName,
          'path' => $geoJsonPath, // Apenas o caminho do GeoJSON
      ]);
  }


  public function get(Request $request)
  {
    return $this->repository->get($request);
  }

  public function getAll()
  {
    return $this->repository->getAll();
  }

  public function getAllCategories()
  {
    return $this->categoryRepository->getAll();
  }

  // Novo método para deletar um item
  public function delete($id)
  {
    // Usar o repositório para deletar o item
    return $this->repository->delete($id);
  }
}
