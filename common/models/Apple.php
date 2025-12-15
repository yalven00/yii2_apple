<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Apple model
 *
 * @property integer $id
 * @property string $color
 * @property integer $created_at
 * @property integer $fallen_at
 * @property string $status
 * @property float $size
 * @property integer $rotten_at
 * @property boolean $is_deleted
 */
class Apple extends ActiveRecord
{
    // Статусы яблока
    const STATUS_ON_TREE = 'on_tree';
    const STATUS_ON_GROUND = 'on_ground';
    const STATUS_ROTTEN = 'rotten';
    const STATUS_EATEN = 'eaten';
    
    // Цвета яблок
    const COLOR_GREEN = 'green';
    const COLOR_RED = 'red';
    const COLOR_YELLOW = 'yellow';
    
    // Время порчи в секундах (5 часов)
    const ROTTEN_TIME = 5 * 60 * 60;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%apple}}';
    }

    /**
     * {@inheritdoc}
     */
public function rules()
{
    return [
        [['color', 'created_at'], 'required'],
        ['color', 'string', 'max' => 50],
        ['color', 'in', 'range' => [self::COLOR_GREEN, self::COLOR_RED, self::COLOR_YELLOW]],
        ['status', 'in', 'range' => [self::STATUS_ON_TREE, self::STATUS_ON_GROUND, self::STATUS_ROTTEN, self::STATUS_EATEN]],
        ['status', 'default', 'value' => self::STATUS_ON_TREE],
        ['size', 'number', 'min' => 0, 'max' => 1],
        ['size', 'default', 'value' => 1.00],
        [['created_at', 'fallen_at', 'rotten_at'], 'integer'],
        ['is_deleted', 'boolean'],
        ['is_deleted', 'default', 'value' => 0], 
    ];
}

public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        if (YII_DEBUG) {
            Yii::info("beforeSave: status = " . var_export($this->status, true) . 
                     ", type = " . gettype($this->status), 'apple');
        }
        
        if ($this->status !== null && !is_string($this->status)) {
            if (YII_DEBUG) {
                Yii::error("status is not string: " . var_export($this->status, true), 'apple');
            }
            $this->status = (string)$this->status;
        }
        
        if ($insert && empty($this->created_at)) {
            $this->created_at = time();
        }
        
        if (is_bool($this->is_deleted)) {
            $this->is_deleted = $this->is_deleted ? 1 : 0;
        }
        
        if ($this->fallen_at === '') {
            $this->fallen_at = null;
        }
        if ($this->rotten_at === '') {
            $this->rotten_at = null;
        }
        
        return true;
    }
    return false;
}

public function afterFind()
{
    parent::afterFind();
    
  
    if (!is_bool($this->is_deleted)) {
        $this->is_deleted = (bool)$this->is_deleted;
    }
    
    $this->checkIfRotten();
}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Цвет',
            'created_at' => 'Дата создания',
            'fallen_at' => 'Дата падения',
            'status' => 'Статус',
            'size' => 'Размер (доля)',
            'rotten_at' => 'Дата порчи',
            'is_deleted' => 'Удалено',
        ];
    }

    /**
     * Создание нового яблока со случайными параметрами
     */
    public static function createRandom()
    {
        $apple = new self();
        $apple->color = self::getRandomColor();
        $apple->created_at = self::getRandomCreationTime();
        $apple->status = self::STATUS_ON_TREE;
        $apple->size = 1.00;
        $apple->is_deleted = false;
        
        return $apple;
    }



   public function safeDelete()
   {
    $this->is_deleted = true;
    return $this->save(false); 
   }

    /**
     * Получить случайный цвет нового яблока
     */
    private static function getRandomColor()
    {
        $colors = [self::COLOR_GREEN, self::COLOR_RED, self::COLOR_YELLOW];
        return $colors[array_rand($colors)];
    }

    /**
     * Получить случайное время создания (от 1 до 30 дней назад)
     */
    private static function getRandomCreationTime()
    {
        return time() - rand(1, 30) * 24 * 60 * 60;
    }

    /**
     * Получить цвет яблока
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Получить размер яблока
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Упасть с дерева
     */
    public function fallToGround()
    {
        if ($this->status !== self::STATUS_ON_TREE) {
            throw new \Exception('Яблоко уже упало или съедено');
        }
        
        $this->status = self::STATUS_ON_GROUND;
        $this->fallen_at = time();
        
        if (!$this->save()) {
            throw new \Exception('Не удалось сохранить состояние яблока');
        }
        
        return true;
    }

    /**
    * Съесть часть яблока
    */
    public function eat($percent)
    {

    Yii::info("eat() вызван для яблока ID: {$this->id}, процент: {$percent}", 'apple');

    $this->checkCanEat();
    

    if (!is_numeric($percent) || $percent <= 0 || $percent > 100) {
        throw new \Exception('Процент должен быть числом от 1 до 100');
    }

    $fraction = $percent / 100;
    
    if ($fraction > $this->size) {
        throw new \Exception('Нельзя съесть больше, чем осталось яблока. Осталось: ' . 
                           $this->getRemainingPercent() . '%, пытаетесь съесть: ' . $percent . '%');
    }
    
    $oldSize = $this->size;
    $this->size -= $fraction;
    $this->size = round($this->size, 2);
    
    Yii::info("Размер изменен: {$oldSize} -> {$this->size}", 'apple');

    if ($this->size <= 0.01) { 
        $this->status = self::STATUS_EATEN;
        $this->is_deleted = true;
        $this->size = 0;
        Yii::info("Яблоко полностью съедено, помечаем как удаленное", 'apple');
    }
    
    if (!$this->save(false)) {
        throw new \Exception('Ошибка сохранения');
    }
    
    Yii::info("eat() успешно завершен для яблока ID: {$this->id}", 'apple');
    return true;
}


     private function checkCanEat()
     {
     Yii::info("checkCanEat() для яблока ID: {$this->id}, статус: {$this->status}", 'apple');
    

      $this->checkIfRotten();
    
     if ($this->status === self::STATUS_ON_TREE) {
        throw new \Exception('Съесть нельзя, яблоко на дереве');
      }
    
      if ($this->status === self::STATUS_ROTTEN) {
        throw new \Exception('Съесть нельзя, яблоко испортилось');
       }
    
      if ($this->status === self::STATUS_EATEN || $this->is_deleted) {
        throw new \Exception('Яблоко уже съедено');
      }
    
      return true;
     }

    /**
     * Проверка статус яблока на испорченность
     */
     private function checkIfRotten()
     {
      if ($this->status === self::STATUS_ON_GROUND && $this->fallen_at) {
        $timeOnGround = time() - $this->fallen_at;
        
        if ($timeOnGround >= self::ROTTEN_TIME && $this->status !== self::STATUS_ROTTEN) {
            $this->status = self::STATUS_ROTTEN;
            $this->rotten_at = time();
            $this->save(false);
          }
       }
      }

    /**
     * Получить статус яблока в читаемом виде
     */
    public function getStatusText()
    {
        $statuses = [
            self::STATUS_ON_TREE => 'На дереве',
            self::STATUS_ON_GROUND => 'На земле',
            self::STATUS_ROTTEN => 'Испорчено',
            self::STATUS_EATEN => 'Съедено',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Получить цвет яблока в читаемом виде
     */
    public function getColorText()
    {
        $colors = [
            self::COLOR_GREEN => 'Зеленое',
            self::COLOR_RED => 'Красное',
            self::COLOR_YELLOW => 'Желтое',
        ];
        
        return $colors[$this->color] ?? $this->color;
    }

    /**
     * Получить время создания в читаемом виде
     */
    public function getCreatedAtText()
    {
        return date('d.m.Y H:i', $this->created_at);
    }

    /**
     * Получить время падения в читаемом виде
     */
    public function getFallenAtText()
    {
        return $this->fallen_at ? date('d.m.Y H:i', $this->fallen_at) : '-';
    }

    /**
     * Получить оставшееся время до порчи
     */
    public function getTimeToRot()
    {
        if ($this->status !== self::STATUS_ON_GROUND || !$this->fallen_at) {
            return null;
        }
        
        $timePassed = time() - $this->fallen_at;
        $timeLeft = self::ROTTEN_TIME - $timePassed;
        
        if ($timeLeft <= 0) {
            return null; // Уже испортилось
        }
        
        $hours = floor($timeLeft / 3600);
        $minutes = floor(($timeLeft % 3600) / 60);
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }

   /**
   * Получить процент оставшегося яблока в процентах
   */
    public function getRemainingPercent()
    {
    if ($this->size === null) {
        return 0;
    }
    return round($this->size * 100);
    }

    /**
     * Проверка, можно ли уронить яблоко
     */
    public function canFall()
    {
        return $this->status === self::STATUS_ON_TREE && !$this->is_deleted;
    }

    /**
     * Проверка, можно ли съесть яблоко
     */
    public function canEat()
    {
        try {
            $this->checkCanEat();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получить CSS класс для цвета яблока
     */
    public function getColorClass()
    {
        return 'apple-' . $this->color;
    }
}