<?php

namespace SenseiTarzan\PacketChecker\Utils;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\DataDecodeException;
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;

final class CommonTypesExtend{

    /** @throws DataDecodeException */
    public static function getString(ByteBufferReader $in, int $maxLength = -1) : string{
        $len = VarInt::readUnsignedInt($in);
        if($maxLength >= 0 && $len > $maxLength){
            throw new DataDecodeException("String length $len exceeds maximum allowed length of $maxLength");
        }
        return $in->readByteArray($maxLength);
    }


    /** @throws DataDecodeException */
    private static function getSkinImage(ByteBufferReader $in) : SkinImage{
        $width = LE::readUnsignedInt($in);
        if($width <= 0){
            throw new DataDecodeException("Skin image width must be positive, got $width");
        }
        $height = LE::readUnsignedInt($in);
        if($height <= 0){
            throw new DataDecodeException("Skin image height must be positive, got $height");
        }
        $realLenSkin = $height * $width * 4;
        $data = self::getString($in, $realLenSkin);
        if(strlen($data) !== $realLenSkin){
            throw new DataDecodeException("Skin image data length " . strlen($data) . " does not match expected length of $realLenSkin for dimensions ${width}x${height}");
        }
        try{
            return new SkinImage($height, $width, $data);
        }catch(\InvalidArgumentException $e){
            throw new PacketDecodeException($e->getMessage(), 0, $e);
        }
    }


    /** @throws DataDecodeException */
    public static function checkSkin(ByteBufferReader $in) : void {
        $skinId = self::getString($in, 64);
        $skinPlayFabId = self::getString($in, 64);
        $skinResourcePatch = self::getString($in, 64);
        $skinData = self::getSkinImage($in);
        $animationCount = LE::readUnsignedInt($in);
        if($animationCount > 256){
            throw new DataDecodeException("Animation count must be between 0 and 100, got $animationCount");
        }
        $animations = [];
        for($i = 0; $i < $animationCount; ++$i){
            $skinImage = self::getSkinImage($in);
            $animationType = LE::readUnsignedInt($in);
            $animationFrames = LE::readFloat($in);
            $expressionType = LE::readUnsignedInt($in);
        }
        $capeData = self::getSkinImage($in);
        $geometryData = CommonTypes::getString($in);
        $geometryDataVersion = CommonTypes::getString($in);
        $animationData = CommonTypes::getString($in);
        $capeId = CommonTypes::getString($in);
        $fullSkinId = CommonTypes::getString($in);
        $armSize = CommonTypes::getString($in);
        $skinColor = CommonTypes::getString($in);
        $personaPieceCount = LE::readUnsignedInt($in);
        $personaPieces = [];
        for($i = 0; $i < $personaPieceCount; ++$i){
            $pieceId = self::getString($in);
            $pieceType = self::getString($in);
            $packId = self::getString($in);
            $isDefaultPiece = CommonTypes::getBool($in);
            $productId = self::getString($in);
        }
        $pieceTintColorCount = LE::readUnsignedInt($in);
        if($pieceTintColorCount > 256){
            throw new DataDecodeException("Piece tint color count must be between 0 and 256, got $pieceTintColorCount");
        }
        for($i = 0; $i < $pieceTintColorCount; ++$i){
            $pieceType = self::getString($in);
            $colorCount = LE::readUnsignedInt($in);
            if($colorCount > 256){
                throw new DataDecodeException("Color count for piece type $pieceType must be between 0 and 256, got $colorCount");
            }
            $colors = [];
            for($j = 0; $j < $colorCount; ++$j){
                $colors[] = self::getString($in);
            }
        }

        $premium = CommonTypes::getBool($in);
        $persona = CommonTypes::getBool($in);
        $capeOnClassic = CommonTypes::getBool($in);
        $isPrimaryUser = CommonTypes::getBool($in);
        $override = CommonTypes::getBool($in);
    }
}