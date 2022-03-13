<?php
$width = $_GET['width'];
$height = $_GET['height'];
//echo $width . $height;
	
//tworzę pustą tablicę 
for($y=0; $y < $height; $y++){
		for($x=0; $x < $width; $x++){
			$grid[$x][$y] = 0;
	}}

//tablica indeksów
$k = 0;
for($y=0; $y < $height; $y++){
		for($x=0; $x < $width; $x++){
			$tab_indeks[$x][$y] = $k;
            $k =  $k + 1;             
	}}


//buduję ściany i dodaję do listy walls
$walls = array();
for($y=0; $y < $height; $y++){
		for($x=0; $x < $width; $x++){
			if ($x + 1 < $width){
            	$k=$x+1;
            	$cell = $x.$y.$k.$y;
            	array_push($walls,$cell);	
            }
            if ($y + 1 < $height){
            	$k=$y+1;
            	$cell = $x.$y.$x.$k;
            	array_push($walls,$cell);	
            }
		}
}

$maze = array();
//-----------------Pick a cell, mark it as part of the maze.----------------
$currentCell_x = 0;
$currentCell_y = 0;

$grid[$currentCell_x][$currentCell_y] = 1;

//---------------Add the walls of the cell to the wall list-----------------
$maze = addWalls($currentCell_x, $currentCell_y, $walls, $maze);

//-----------------While there are walls in the list:----------------------
while(sizeof($walls) > 0){
//------------Pick a random wall from the list.------------ 
  $randomWall = $maze[rand(0,sizeof($maze)-1)]; //zwraca ścianę, np 0010
  //wczytuję współrzędne komórek podzielonych wylosowaną ścianą
  $x1 = substr($randomWall, 0, 1);  
  $y1 = substr($randomWall, 1, 1); 
  $x2 = substr($randomWall, -2,1);  
  $y2 = substr($randomWall, -1); 

//----------If only one of the cells that the wall divides is visited, then make the wall a passage and mark the unvisited cell as part of the maze.-----------
  if($grid[$x1][$y1] == 0 || $grid[$x2][$y2] == 0) { //jedna z komórek jest jeszcze nieodwiedzona
    if($grid[$x1][$y1] == 0){
    	$grid[$x1][$y1] = 1;
        $currentCell_x = $x1;
        $currentCell_y = $y1;      
    }
    if ($grid[$x2][$y2] == 0){
    	$grid[$x2][$y2] = 1;
        $currentCell_x = $x2;
        $currentCell_y = $y2;        
    }
    
      $maze = \array_diff($maze, [$randomWall]);
	  $maze = array_values($maze);
      $walls = \array_diff($walls, [$randomWall]);
	  $walls = array_values($walls);
      
      //-----------Add the neighboring walls of the cell to the wall list.-----------
      
	  $maze = addWalls($currentCell_x, $currentCell_y, $walls, $maze);
  } else{
  
      $walls = \array_diff($walls, [$randomWall]);
      $walls = array_values($walls);
        }
}
 
$result = draw($maze, $tab_indeks, $height, $width);


echo writeResult($result,$width,$height,$tab_indeks);


function addWalls($currentCell_x, $currentCell_y, $walls, $maze){
    for($i=0;$i<sizeof($walls);$i++){
        if (strpos($walls[$i], $currentCell_x.$currentCell_y) !== false){ // znajduje sąsiada w walls
        	//if(!(strval($currentCell_x) === substr($walls[$i], 1, 1) && strval($currentCell_y) === substr($walls[$i], -2, 1))){
            if((strval($currentCell_x) === substr($walls[$i], 0, 1) && strval($currentCell_y) === substr($walls[$i], 1, 1)) || (strval($currentCell_x) === substr($walls[$i], -2, 1) && strval($currentCell_y) === substr($walls[$i], -1))){
            // jeśli znalezione współrzędne nie stanowią środkowej części walls[i]
            	if (!in_array($walls[$i], $maze)) { // ściany nie ma jeszcze w maze
          			array_push($maze,$walls[$i]);
            	}
        	}
        }
        
     } return $maze;
}

function draw($maze, $tab_indeks, $height, $width){
	//$k = 0;
    //echo tab_indeks[2][1];
	$Connect = array();
	for($y=0; $y < $height; $y++){		
		for($x=0; $x < $width; $x++){
			$L = array();
            //echo "ind $tab_indeks[$x][$y]";
            //echo("<br>");
			if($x+1<$width){     
            	$z = $x+1;
				if(isInMaze($maze, $x.$y.$z.$y)===false){
					$ind = $tab_indeks[$z][$y];
					array_push($L,$ind);
				}
			}
			if($y+1<$height){
            $z = $y+1;
				if(isInMaze($maze, $x.$y.$x.$z)===false){
					$ind = $tab_indeks[$x][$z];
					array_push($L,$ind);
				} 
			}
			if($x>0){
            	$z = $x-1;
				if(isInMaze($maze, $z.$y.$x.$y)===false){
					$ind = $tab_indeks[$z][$y];
					array_push($L,$ind);
				} 
			}
			if($y>0){
            $z = $y-1;
				if(isInMaze($maze, $x.$z.$x.$y)===false){
					$ind = $tab_indeks[$x][$z];
					array_push($L,$ind);
				} 
			}
			
			array_push($Connect, $L); 
			//$k = $k + 1;			
	}
	
	
	
	}
return $Connect;
}


function isInMaze($maze, $str){
	for($i=0;$i<sizeof($maze);$i++){
    	if (strpos($maze[$i], $str) !== false) return true;
    } 
    return false;
}

function writeResult($result,$width,$height,$tab_indeks){
		$str = "{ \"width\": $width, \"height\": $height, \"paths\": [ ";
		for($i=0;$i<sizeof($result);$i++){
			// i jest indeksem komórki, więc na podstawie tab_index można zdobyć współrzędne x i y
			$tab = getCoordinates($i,$tab_indeks,$width,$height);
            //$tab = [1,2];
			$x = $tab[0]; $y = $tab[1];
			$str = $str."{ \"x\": $x, \"y\": $y, \"L\": [";
			for($j=0;$j<sizeof($result[$i]);$j++){
            	$l = strval($result[$i][$j]);
				$str = $str." $l,";
			}
			$str = substr($str, 0, -1); // usuwam przecinek z końca
			$str = $str." ] }, ";
		}
        $str = substr($str, 0, -2);
		return $str." ] }";
}


function getCoordinates($i,$tab_indeks,$width,$height){
	$tab = array();
	for($y=0; $y < $height; $y++){		
		for($x=0; $x < $width; $x++){
			if($tab_indeks[$x][$y] === $i){
				array_push($tab,$x);
				array_push($tab,$y);
				return $tab;
			}
		}
	}
}


	//localhost/generator.php?width=3&height=4

