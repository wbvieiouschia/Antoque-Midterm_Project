<?php
// models/Report.php
class Report {
    private Database $db;
    private Farmer $farmerModel;
    private Farm $farmModel;
    private Planting $plantingModel;
    private Harvest $harvestModel;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->farmerModel = new Farmer($db);
        $this->farmModel = new Farm($db);
        $this->plantingModel = new Planting($db);
        $this->harvestModel = new Harvest($db);
    }

    public function farmersAndFarms(array $filters = []): array {
        $farmers = $this->farmerModel->all($filters);
        $farmsByFarmer = [];
        foreach ($this->farmModel->all([], ['limit' => 1000]) as $fm) {
            $farmsByFarmer[$fm['farmer_id']][] = $fm;
        }
        return [
            'farmers' => $farmers,
            'farmsByFarmer' => $farmsByFarmer,
        ];
    }

    public function plantings(array $filters = [], array $options = []): array {
        return $this->plantingModel->all($filters, $options);
    }

    public function harvests(array $filters = [], array $options = []): array {
        return $this->harvestModel->all($filters, $options);
    }
}